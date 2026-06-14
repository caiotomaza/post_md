import { renderMarkdown } from './editor.js';
import { buildTabs, restoreTabs, persistTabs } from './tabs.js';
import { renderTagBadge } from './tags.js';

export default function workspace() {
    return {
        // ── Rail & sidebar ──────────────────────────────────────────────────
        railSection: 'files',
        sidebarOpen: true,
        darkMode: true,
        openIn: 'new-tab',

        // ── Tree state ──────────────────────────────────────────────────────
        tree: { folders: [], notes: [] },
        treeItems: [], // flattened for rendering
        selectedFolderId: null,
        selectedNoteId: null,

        // ── Tabs ────────────────────────────────────────────────────────────
        tabs: [],
        activeTabId: null,
        _saveTimers: {},
        _lastSavedContent: {},

        // ── Tags ────────────────────────────────────────────────────────────
        allTags: [],
        tagsPopoverOpen: false,

        // ── UI state ────────────────────────────────────────────────────────
        editorContent: '',
        renderedContent: '',
        cursorInfo: '',

        contextMenu: { visible: false, x: 0, y: 0, items: [] },
        inlineEdit: { visible: false, x: 0, y: 0, w: 180, value: '', error: '', _resolve: null },
        tagModal: { open: false, isEdit: false, id: null, name: '', display_mode: 'color', color_hex: '', emoji: '', error: '' },
        moveModal: { open: false, itemType: null, itemId: null },
        confirmModal: { open: false, message: '', action: null },

        commonEmojis: ['📌','📚','🧩','✅','⚡','🔥','💡','🎯','📝','🔖','⭐','🏷️','🗂️','📁','💼','🔑','🛠️','🚀','🎨','🧪'],

        // ── Computed ─────────────────────────────────────────────────────────
        get activeTab() {
            return this.tabs.find(t => t.id === this.activeTabId) || null;
        },

        get flatFolders() {
            const result = [];
            const flatten = (folders, depth = 0) => {
                for (const f of folders) {
                    result.push({ ...f, _depth: depth, _isDescendant: false });
                    flatten(f.children || [], depth + 1);
                }
            };
            flatten(this.tree.folders);

            if (this.moveModal.itemType === 'folder') {
                const descIds = this._getDescendantIds(this.moveModal.itemId);
                for (const f of result) {
                    f._isDescendant = descIds.has(f.id);
                }
            }
            return result;
        },

        get saveStatusText() {
            const tab = this.activeTab;
            if (!tab) return '';
            switch (tab.saveStatus) {
                case 'editing': return 'Editando';
                case 'saving': return 'Salvando...';
                case 'saved': return 'Salvo ✓';
                case 'error': return 'Erro ao salvar';
                default: return '';
            }
        },

        // ── Init ─────────────────────────────────────────────────────────────
        init() {
            const ws = window.__WORKSPACE__;
            this.tree = ws.tree;
            this.allTags = ws.tags || [];

            // Setup axios CSRF
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = ws.csrfToken;

            this._buildTreeItems();
            this._loadSettings();
            this._restoreTabs();

            // Watch editor content
            this.$watch('editorContent', (val) => {
                const tab = this.activeTab;
                if (!tab) return;
                if (val === this._lastSavedContent[tab.id]) return;
                tab.content = val;
                tab.isDirty = true;
                tab.saveStatus = 'editing';
                this._scheduleSave(tab);
            });

            // Watch view mode for re-render
            this.$watch('activeTabId', () => {
                this._syncEditorToTab();
            });
        },

        // ── Settings ─────────────────────────────────────────────────────────
        _loadSettings() {
            try {
                const s = JSON.parse(localStorage.getItem('pmd_settings') || '{}');
                this.darkMode = s.darkMode !== false;
                this.openIn = s.openIn || 'new-tab';
            } catch (e) { /* ignore */ }
        },

        saveSettings() {
            localStorage.setItem('pmd_settings', JSON.stringify({
                darkMode: this.darkMode,
                openIn: this.openIn,
            }));
        },

        toggleTheme() {
            this.darkMode = !this.darkMode;
            this.saveSettings();
        },

        // ── Tree building ─────────────────────────────────────────────────────
        _buildTreeItems() {
            const items = [];
            const buildFolder = (folder, depth) => {
                items.push({ type: 'folder', data: folder, depth });
                if (folder.is_expanded) {
                    for (const child of (folder.children || [])) {
                        buildFolder(child, depth + 1);
                    }
                    for (const note of (folder.notes || [])) {
                        items.push({ type: 'note', data: note, depth: depth + 1 });
                    }
                }
            };
            for (const f of (this.tree.folders || [])) buildFolder(f, 0);
            for (const n of (this.tree.notes || [])) items.push({ type: 'note', data: n, depth: 0 });

            this.treeItems = items.map((item, i) => ({ ...item, _key: `${item.type}-${item.data.id}` }));
        },

        renderTreeItem(item) {
            const { type, data, depth } = item;
            const indent = depth * 16;
            const isActive = type === 'note' && data.id === this.selectedNoteId;
            const isFolderActive = type === 'folder' && data.id === this.selectedFolderId;
            const activeClass = (isActive || isFolderActive) ? 'active' : '';

            let lines = '';
            for (let i = 0; i < depth; i++) lines += '<span class="pmd-tree-line"></span>';

            if (type === 'folder') {
                const expanded = data.is_expanded;
                const chevronClass = expanded ? 'pmd-tree-chevron expanded' : 'pmd-tree-chevron';
                const chevronSvg = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>`;
                const folderSvg = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>`;

                return `<div class="pmd-tree-row ${activeClass}"
                    data-type="folder" data-id="${data.id}"
                    onclick="Alpine.store && window._pmdWs.selectFolder(event, ${data.id})"
                    ondblclick="window._pmdWs.startRename(event, 'folder', ${data.id})"
                    oncontextmenu="window._pmdWs.showContextMenu(event, 'folder', ${data.id})">
                    <span class="pmd-tree-indent">${lines}</span>
                    <span class="${chevronClass}" onclick="event.stopPropagation(); window._pmdWs.toggleFolder(${data.id})">${chevronSvg}</span>
                    <span class="pmd-tree-icon">${folderSvg}</span>
                    <span class="pmd-tree-name" title="${this._esc(data.name)}">${this._esc(data.name)}</span>
                    <span class="pmd-tree-actions">
                        <button class="pmd-icon-btn-sm" onclick="event.stopPropagation(); window._pmdWs.createNote(${data.id})" title="Nova nota">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                    </span>
                </div>`;
            } else {
                const noteSvg = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>`;
                const tags = data.tags || [];
                const visibleTags = tags.slice(0, 3);
                const extraCount = tags.length > 3 ? `<span class="pmd-tree-tag-more">+${tags.length - 3}</span>` : '';
                const tagHtml = visibleTags.map(t => {
                    if (t.display_mode === 'color') return `<span class="pmd-tree-tag-dot" style="background:${t.color_hex}" title="${this._esc(t.name)}"></span>`;
                    if (t.display_mode === 'emoji') return `<span style="font-size:10px" title="${this._esc(t.name)}">${t.emoji}</span>`;
                    return `<span class="pmd-tree-tag-dot" style="background:${t.color_hex}" title="${this._esc(t.name)}"></span><span style="font-size:10px">${t.emoji}</span>`;
                }).join('') + extraCount;

                return `<div class="pmd-tree-row ${activeClass}"
                    data-type="note" data-id="${data.id}"
                    onclick="window._pmdWs.openNote(${data.id})"
                    ondblclick="window._pmdWs.startRename(event, 'note', ${data.id})"
                    oncontextmenu="window._pmdWs.showContextMenu(event, 'note', ${data.id})">
                    <span class="pmd-tree-indent">${lines}</span>
                    <span class="pmd-tree-chevron" style="visibility:hidden">${`<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>`}</span>
                    <span class="pmd-tree-icon">${noteSvg}</span>
                    <span class="pmd-tree-name" title="${this._esc(data.name)}">${this._esc(data.name)}</span>
                    <span class="pmd-tree-tags">${tagHtml}</span>
                </div>`;
            }
        },

        _esc(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        },

        // ── Tree actions ──────────────────────────────────────────────────────
        selectFolder(event, folderId) {
            this.selectedFolderId = folderId;
            this.selectedNoteId = null;
        },

        async toggleFolder(folderId) {
            const folder = this._findFolder(folderId);
            if (!folder) return;
            folder.is_expanded = !folder.is_expanded;
            this._buildTreeItems();
            try {
                await window.axios.post(`/folders/${folderId}/toggle`);
            } catch (e) { /* ignore persistence error */ }
        },

        collapseAll() {
            const collapse = (folders) => {
                for (const f of folders) {
                    f.is_expanded = false;
                    collapse(f.children || []);
                }
            };
            collapse(this.tree.folders);
            this._buildTreeItems();
        },

        // ── Note opening ──────────────────────────────────────────────────────
        async openNote(noteId) {
            this.selectedNoteId = noteId;
            this.selectedFolderId = null;

            const existingTab = this.tabs.find(t => t.noteId === noteId);
            if (existingTab) {
                this.activateTab(existingTab.id);
                return;
            }

            await this._loadAndOpenNote(noteId);
        },

        async _loadAndOpenNote(noteId) {
            try {
                const res = await window.axios.get(`/notes/${noteId}`);
                const note = res.data.data;

                if (this.openIn === 'current-tab' && this.activeTab) {
                    await this._flushSave(this.activeTabId);
                    this._replaceTab(note);
                } else {
                    this._addTab(note);
                }
            } catch (e) {
                console.error('Erro ao carregar nota', e);
            }
        },

        _addTab(note) {
            const tab = this._makeTab(note);
            this.tabs.push(tab);
            this.activateTab(tab.id);
            persistTabs(this.tabs, this.activeTabId);
        },

        _replaceTab(note) {
            const tab = this._makeTab(note);
            const idx = this.tabs.findIndex(t => t.id === this.activeTabId);
            if (idx >= 0) {
                this.tabs.splice(idx, 1, tab);
            } else {
                this.tabs.push(tab);
            }
            this.activateTab(tab.id);
            persistTabs(this.tabs, this.activeTabId);
        },

        _makeTab(note) {
            return {
                id: `tab-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                noteId: note.id,
                name: note.name,
                content: note.content || '',
                folderName: note.folder_name || null,
                tags: note.tags || [],
                isDirty: false,
                saveStatus: 'saved',
                viewMode: this._loadNoteViewMode(note.id),
            };
        },

        activateTab(tabId) {
            if (this.activeTabId && this.activeTabId !== tabId) {
                this._flushSave(this.activeTabId);
            }
            this.activeTabId = tabId;
            this._syncEditorToTab();
            persistTabs(this.tabs, this.activeTabId);
        },

        async closeTab(tabId) {
            await this._flushSave(tabId);
            const idx = this.tabs.findIndex(t => t.id === tabId);
            if (idx < 0) return;
            this.tabs.splice(idx, 1);

            if (this.activeTabId === tabId) {
                const newActive = this.tabs[Math.min(idx, this.tabs.length - 1)];
                this.activeTabId = newActive ? newActive.id : null;
                this._syncEditorToTab();
            }
            persistTabs(this.tabs, this.activeTabId);
        },

        _syncEditorToTab() {
            const tab = this.activeTab;
            if (!tab) {
                this.editorContent = '';
                this.renderedContent = '';
                return;
            }
            this.editorContent = tab.content || '';
            this._lastSavedContent[tab.id] = tab.content || '';
            this._updateRendered();

            const savedMode = this._loadNoteViewMode(tab.noteId);
            if (savedMode) tab.viewMode = savedMode;
        },

        // ── Save ──────────────────────────────────────────────────────────────
        onEditorInput() {
            this._updateRendered();
            this._updateCursorInfo();
        },

        _scheduleSave(tab) {
            clearTimeout(this._saveTimers[tab.id]);
            this._saveTimers[tab.id] = setTimeout(() => {
                this._doSave(tab);
            }, 850);
        },

        async _doSave(tab) {
            if (!tab.isDirty) return;
            const content = tab.content;
            tab.saveStatus = 'saving';
            try {
                await window.axios.patch(`/notes/${tab.noteId}`, { content });
                this._lastSavedContent[tab.id] = content;
                tab.isDirty = false;
                tab.saveStatus = 'saved';
            } catch (e) {
                tab.saveStatus = 'error';
                console.error('Erro ao salvar', e);
            }
        },

        async _flushSave(tabId) {
            const tab = this.tabs.find(t => t.id === tabId);
            if (!tab || !tab.isDirty) return;
            clearTimeout(this._saveTimers[tabId]);
            await this._doSave(tab);
        },

        async retrySave() {
            const tab = this.activeTab;
            if (!tab) return;
            tab.isDirty = true;
            await this._doSave(tab);
        },

        // ── View mode ─────────────────────────────────────────────────────────
        async setViewMode(mode) {
            const tab = this.activeTab;
            if (!tab) return;
            if (tab.isDirty) await this._doSave(tab);
            tab.viewMode = mode;
            this._saveNoteViewMode(tab.noteId, mode);
            if (mode === 'reading') this._updateRendered();
        },

        _loadNoteViewMode(noteId) {
            return localStorage.getItem(`pmd_vm_${noteId}`) || 'source';
        },

        _saveNoteViewMode(noteId, mode) {
            localStorage.setItem(`pmd_vm_${noteId}`, mode);
        },

        _updateRendered() {
            if (this.activeTab?.viewMode === 'reading') {
                this.renderedContent = renderMarkdown(this.editorContent);
            }
        },

        _updateCursorInfo() {
            const ta = this.$refs.editor;
            if (!ta) return;
            const val = ta.value;
            const pos = ta.selectionStart || 0;
            const lines = val.substring(0, pos).split('\n');
            const line = lines.length;
            const col = lines[lines.length - 1].length + 1;
            this.cursorInfo = `L${line}:C${col}`;
        },

        // ── Create note ───────────────────────────────────────────────────────
        async createNote(folderId) {
            const name = prompt('Nome da nota (sem .md):');
            if (!name || !name.trim()) return;

            try {
                const res = await window.axios.post('/notes', {
                    name: name.trim(),
                    folder_id: folderId || null,
                    content: '',
                });
                const note = res.data.data;
                await this._refreshTree();
                await this._loadAndOpenNote(note.id);
            } catch (e) {
                const msg = e.response?.data?.errors?.name?.[0] || 'Erro ao criar nota.';
                alert(msg);
            }
        },

        // ── Create folder ─────────────────────────────────────────────────────
        async createFolder(parentId) {
            const name = prompt('Nome da pasta:');
            if (!name || !name.trim()) return;

            try {
                await window.axios.post('/folders', {
                    name: name.trim(),
                    parent_id: parentId || null,
                });
                await this._refreshTree();
            } catch (e) {
                const msg = e.response?.data?.errors?.name?.[0] || 'Erro ao criar pasta.';
                alert(msg);
            }
        },

        // ── Rename (inline) ───────────────────────────────────────────────────
        startRename(event, type, id) {
            event.stopPropagation();
            const row = event.currentTarget;
            const rect = row.getBoundingClientRect();
            const item = type === 'folder' ? this._findFolder(id) : this._findNote(id);
            if (!item) return;

            this.inlineEdit = {
                visible: true,
                x: rect.left + 46,
                y: rect.top,
                w: Math.max(160, rect.width - 60),
                value: item.name,
                error: '',
                _type: type,
                _id: id,
                _original: item.name,
            };

            this.$nextTick(() => {
                const input = this.$refs.inlineInput;
                if (input) {
                    input.focus();
                    const dotPos = input.value.lastIndexOf('.');
                    input.setSelectionRange(0, dotPos > 0 ? dotPos : input.value.length);
                }
            });
        },

        async confirmInlineEdit() {
            if (!this.inlineEdit.visible) return;
            const { _type, _id, value, _original } = this.inlineEdit;
            if (!value.trim() || value === _original) {
                this.cancelInlineEdit();
                return;
            }

            try {
                if (_type === 'folder') {
                    await window.axios.patch(`/folders/${_id}`, { name: value.trim() });
                    const folder = this._findFolder(_id);
                    if (folder) folder.name = value.trim();
                } else {
                    const res = await window.axios.patch(`/notes/${_id}`, { name: value.trim() });
                    const note = this._findNote(_id);
                    if (note) note.name = res.data.data.name;
                    const tab = this.tabs.find(t => t.noteId === _id);
                    if (tab) tab.name = res.data.data.name;
                }
                this.cancelInlineEdit();
                this._buildTreeItems();
            } catch (e) {
                const msg = e.response?.data?.errors?.name?.[0]
                    || e.response?.data?.message
                    || 'Erro ao renomear.';
                this.inlineEdit.error = msg;
            }
        },

        cancelInlineEdit() {
            this.inlineEdit.visible = false;
            this.inlineEdit.error = '';
        },

        // ── Delete ────────────────────────────────────────────────────────────
        deleteFolder(folderId) {
            const folder = this._findFolder(folderId);
            this.confirmModal = {
                open: true,
                message: `Excluir a pasta "${folder?.name}"? As subpastas e notas serão excluídas.`,
                action: async () => {
                    try {
                        await window.axios.delete(`/folders/${folderId}`);
                        await this._refreshTree();
                    } catch (e) { alert('Erro ao excluir pasta.'); }
                },
            };
        },

        deleteNote(noteId) {
            const note = this._findNote(noteId);
            this.confirmModal = {
                open: true,
                message: `Excluir a nota "${note?.name}"?`,
                action: async () => {
                    try {
                        await window.axios.delete(`/notes/${noteId}`);
                        const tabIdx = this.tabs.findIndex(t => t.noteId === noteId);
                        if (tabIdx >= 0) await this.closeTab(this.tabs[tabIdx].id);
                        await this._refreshTree();
                    } catch (e) { alert('Erro ao excluir nota.'); }
                },
            };
        },

        // ── Move ──────────────────────────────────────────────────────────────
        openMoveDialog(type, id) {
            this.moveModal = { open: true, itemType: type, itemId: id };
        },

        async executeMove(targetId) {
            const { itemType, itemId } = this.moveModal;
            this.moveModal.open = false;
            try {
                if (itemType === 'folder') {
                    await window.axios.post(`/folders/${itemId}/move`, { target_parent_id: targetId });
                } else {
                    await window.axios.post(`/notes/${itemId}/move`, { target_folder_id: targetId });
                }
                await this._refreshTree();
            } catch (e) {
                alert(e.response?.data?.message || 'Erro ao mover.');
            }
        },

        // ── Context menu ──────────────────────────────────────────────────────
        showContextMenu(event, type, id) {
            event.preventDefault();
            event.stopPropagation();

            const items = [];
            if (type === 'folder') {
                items.push({ label: 'Nova nota aqui', action: () => this.createNote(id) });
                items.push({ label: 'Nova subpasta', action: () => this._createSubFolder(id) });
                items.push({ label: 'Renomear', action: () => this.startRename(event, 'folder', id) });
                items.push({ label: 'Mover para...', action: () => this.openMoveDialog('folder', id) });
                items.push({ label: 'Excluir', danger: true, action: () => this.deleteFolder(id) });
            } else {
                items.push({ label: 'Abrir', action: () => this.openNote(id) });
                items.push({ label: 'Renomear', action: () => this.startRename(event, 'note', id) });
                items.push({ label: 'Mover para...', action: () => this.openMoveDialog('note', id) });
                items.push({ label: 'Excluir', danger: true, action: () => this.deleteNote(id) });
            }

            const margin = 8;
            let x = event.clientX;
            let y = event.clientY;
            if (x + 180 > window.innerWidth) x = window.innerWidth - 180 - margin;
            if (y + items.length * 36 > window.innerHeight) y = window.innerHeight - items.length * 36 - margin;

            this.contextMenu = { visible: true, x, y, items };
        },

        async _createSubFolder(parentId) {
            const name = prompt('Nome da subpasta:');
            if (!name || !name.trim()) return;
            try {
                await window.axios.post('/folders', { name: name.trim(), parent_id: parentId });
                await this._refreshTree();
            } catch (e) {
                alert(e.response?.data?.errors?.name?.[0] || 'Erro ao criar subpasta.');
            }
        },

        // ── Tags ──────────────────────────────────────────────────────────────
        renderTagBadge(tag) {
            return renderTagBadge(tag);
        },

        toggleTagsPopover() {
            this.tagsPopoverOpen = !this.tagsPopoverOpen;
        },

        async toggleNoteTag(tag, checked) {
            const tab = this.activeTab;
            if (!tab) return;
            try {
                if (checked) {
                    const res = await window.axios.post(`/notes/${tab.noteId}/tags/${tag.id}`);
                    tab.tags = res.data.data;
                } else {
                    const res = await window.axios.delete(`/notes/${tab.noteId}/tags/${tag.id}`);
                    tab.tags = res.data.data;
                }
                this._updateNoteTagsInTree(tab.noteId, tab.tags);
            } catch (e) {
                console.error('Erro ao associar tag', e);
            }
        },

        _updateNoteTagsInTree(noteId, tags) {
            const note = this._findNote(noteId);
            if (note) {
                note.tags = tags;
                this._buildTreeItems();
            }
        },

        openCreateTag() {
            this.tagModal = { open: true, isEdit: false, id: null, name: '', display_mode: 'color', color_hex: '', emoji: '', error: '' };
        },

        openEditTag(tag) {
            this.tagModal = {
                open: true,
                isEdit: true,
                id: tag.id,
                name: tag.name,
                display_mode: tag.display_mode,
                color_hex: tag.color_hex || '',
                emoji: tag.emoji || '',
                error: '',
            };
        },

        async saveTag() {
            const m = this.tagModal;
            m.error = '';
            const payload = {
                name: m.name,
                display_mode: m.display_mode,
                color_hex: m.color_hex || null,
                emoji: m.emoji || null,
            };
            try {
                if (m.isEdit) {
                    const res = await window.axios.patch(`/tags/${m.id}`, payload);
                    const updated = res.data.data;
                    const idx = this.allTags.findIndex(t => t.id === m.id);
                    if (idx >= 0) this.allTags[idx] = updated;
                } else {
                    const res = await window.axios.post('/tags', payload);
                    this.allTags.push(res.data.data);
                }
                m.open = false;
                this._buildTreeItems();
            } catch (e) {
                const errors = e.response?.data?.errors || {};
                const messages = Object.values(errors).flat();
                m.error = messages[0] || e.response?.data?.message || 'Erro ao salvar tag.';
            }
        },

        async deleteTag(tag) {
            this.confirmModal = {
                open: true,
                message: `Excluir a tag "${tag.name}"? Ela será removida de todas as notas.`,
                action: async () => {
                    try {
                        await window.axios.delete(`/tags/${tag.id}`);
                        this.allTags = this.allTags.filter(t => t.id !== tag.id);
                        await this._refreshTree();
                    } catch (e) { alert('Erro ao excluir tag.'); }
                },
            };
        },

        // ── Markdown toolbar ──────────────────────────────────────────────────
        insertMd(type) {
            const ta = this.$refs.editor;
            if (!ta) return;
            ta.focus();
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.substring(start, end);
            const before = ta.value.substring(0, start);
            const after = ta.value.substring(end);

            let insert = '';
            let cursorOffset = 0;
            let wrapBefore = '';
            let wrapAfter = '';
            let isWrap = false;

            switch (type) {
                case 'heading':    wrapBefore = '## '; isWrap = false; cursorOffset = 3; break;
                case 'bold':       wrapBefore = '**'; wrapAfter = '**'; isWrap = true; break;
                case 'italic':     wrapBefore = '*'; wrapAfter = '*'; isWrap = true; break;
                case 'strikethrough': wrapBefore = '~~'; wrapAfter = '~~'; isWrap = true; break;
                case 'ul':         wrapBefore = '- '; isWrap = false; cursorOffset = 2; break;
                case 'ol':         wrapBefore = '1. '; isWrap = false; cursorOffset = 3; break;
                case 'checklist': wrapBefore = '- [ ] '; isWrap = false; cursorOffset = 6; break;
                case 'blockquote': wrapBefore = '> '; isWrap = false; cursorOffset = 2; break;
                case 'link':       wrapBefore = '['; wrapAfter = '](url)'; isWrap = true; break;
                case 'code':       wrapBefore = '`'; wrapAfter = '`'; isWrap = true; break;
                case 'codeblock': insert = '```\n' + (selected || 'código') + '\n```'; break;
                case 'hr':        insert = '\n---\n'; break;
            }

            let newText, newCursorPos;

            if (insert) {
                newText = before + insert + after;
                newCursorPos = start + insert.length;
            } else if (isWrap && selected) {
                newText = before + wrapBefore + selected + wrapAfter + after;
                newCursorPos = start + wrapBefore.length + selected.length + wrapAfter.length;
            } else {
                const placeholder = selected || '';
                newText = before + wrapBefore + placeholder + wrapAfter + after;
                newCursorPos = start + wrapBefore.length + placeholder.length + wrapAfter.length;
                if (!placeholder && !wrapAfter) newCursorPos = start + cursorOffset;
            }

            ta.value = newText;
            ta.setSelectionRange(newCursorPos, newCursorPos);

            this.editorContent = newText;
            const tab = this.activeTab;
            if (tab) {
                tab.content = newText;
                tab.isDirty = true;
                tab.saveStatus = 'editing';
                this._scheduleSave(tab);
            }
            this._updateCursorInfo();
        },

        insertTab() {
            const ta = this.$refs.editor;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const newText = ta.value.substring(0, start) + '    ' + ta.value.substring(end);
            ta.value = newText;
            ta.setSelectionRange(start + 4, start + 4);
            this.editorContent = newText;
        },

        // ── Tab persistence ───────────────────────────────────────────────────
        _restoreTabs() {
            const saved = restoreTabs();
            if (!saved || saved.tabs.length === 0) return;

            const tabIds = saved.tabs.map(t => t.noteId);
            if (tabIds.length === 0) return;

            Promise.all(tabIds.map(id => window.axios.get(`/notes/${id}`).catch(() => null)))
                .then(results => {
                    const validTabs = [];
                    for (let i = 0; i < results.length; i++) {
                        if (results[i]) {
                            const note = results[i].data.data;
                            validTabs.push(this._makeTab(note));
                        }
                    }
                    this.tabs = validTabs;
                    const activeTab = validTabs.find(t => t.noteId == saved.activeNoteId);
                    if (activeTab) {
                        this.activateTab(activeTab.id);
                    } else if (validTabs.length > 0) {
                        this.activateTab(validTabs[0].id);
                    }
                });
        },

        // ── Tree helpers ──────────────────────────────────────────────────────
        async _refreshTree() {
            try {
                const res = await window.axios.get('/folders/tree');
                this.tree = res.data.data;
                this._buildTreeItems();
            } catch (e) {
                console.error('Erro ao atualizar árvore', e);
            }
        },

        _findFolder(id, folders = null) {
            const list = folders || this.tree.folders;
            for (const f of list) {
                if (f.id === id) return f;
                const found = this._findFolder(id, f.children || []);
                if (found) return found;
            }
            return null;
        },

        _findNote(id, folders = null) {
            const notes = (folders === null) ? this.tree.notes : [];
            for (const n of notes) { if (n.id === id) return n; }

            const list = folders || this.tree.folders;
            for (const f of list) {
                for (const n of (f.notes || [])) { if (n.id === id) return n; }
                const found = this._findNote(id, f.children || []);
                if (found) return found;
            }
            return null;
        },

        _getDescendantIds(folderId, result = new Set()) {
            const folder = this._findFolder(folderId);
            if (!folder) return result;
            for (const child of (folder.children || [])) {
                result.add(child.id);
                this._getDescendantIds(child.id, result);
            }
            return result;
        },
    };
}

// Expose reference for HTML event handlers
document.addEventListener('alpine:init', () => {
    // noop, workspace is registered in app.js
});
