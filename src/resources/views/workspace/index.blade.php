@extends('layouts.app')

@section('content')
<div id="workspace"
    x-data="workspace()"
    x-init="init()"
    :class="darkMode ? 'dark' : 'light'">

    {{-- Rail --}}
    <aside class="pmd-rail">
        <div class="pmd-rail-logo" title="post_md">
            <span class="pmd-logo-text">p_</span>
        </div>
        <nav class="pmd-rail-nav">
            <button class="pmd-rail-btn" :class="{ active: railSection === 'files' }"
                @click="railSection = 'files'; sidebarOpen = true"
                title="Arquivos">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 3h8l2 2h8v14H3z"/>
                </svg>
            </button>
            <button class="pmd-rail-btn" :class="{ active: railSection === 'search' }"
                @click="railSection = 'search'; sidebarOpen = true"
                title="Busca (em breve)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </button>
            <button class="pmd-rail-btn" :class="{ active: railSection === 'tags' }"
                @click="railSection = 'tags'; sidebarOpen = true"
                title="Tags">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <circle cx="7" cy="7" r="1.5" fill="currentColor"/>
                </svg>
            </button>
            <button class="pmd-rail-btn" :class="{ active: railSection === 'settings' }"
                @click="railSection = 'settings'; sidebarOpen = true"
                title="Configurações">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
            </button>
        </nav>
        <div class="pmd-rail-bottom">
            <button class="pmd-rail-btn" @click="toggleTheme()" :title="darkMode ? 'Mudar para claro' : 'Mudar para escuro'">
                <svg x-show="darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg x-show="!darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>
        </div>
    </aside>

    {{-- Sidebar --}}
    <aside class="pmd-sidebar" :class="{ 'pmd-sidebar--hidden': !sidebarOpen }">
        {{-- FILES --}}
        <div x-show="railSection === 'files'" class="pmd-sidebar-panel">
            <div class="pmd-tree-toolbar">
                <button class="pmd-icon-btn" @click="createNote(null)" title="Nova nota">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/>
                        <line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>
                    </svg>
                </button>
                <button class="pmd-icon-btn" @click="createFolder(null)" title="Nova pasta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        <line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>
                    </svg>
                </button>
                <button class="pmd-icon-btn" @click="collapseAll()" title="Recolher tudo">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="4,14 12,6 20,14"/>
                        <line x1="4" y1="19" x2="20" y2="19"/>
                    </svg>
                </button>
                <button class="pmd-icon-btn" @click="sidebarOpen = false" title="Fechar painel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"/>
                    </svg>
                </button>
            </div>

            <div class="pmd-tree" x-ref="treeContainer">
                {{-- Root label --}}
                <div class="pmd-tree-root-label">post_md</div>

                {{-- Tree items --}}
                <div class="pmd-tree-items">
                    <template x-for="item in treeItems" :key="item._key">
                        <div x-html="renderTreeItem(item)"></div>
                    </template>
                </div>

                {{-- Empty state --}}
                <div x-show="treeItems.length === 0" class="pmd-empty-tree">
                    <p>Nenhuma nota ainda.</p>
                    <button class="pmd-btn-primary" @click="createNote(null)">Nova nota</button>
                </div>
            </div>
        </div>

        {{-- SEARCH (visual only) --}}
        <div x-show="railSection === 'search'" class="pmd-sidebar-panel">
            <div class="pmd-panel-header">Busca</div>
            <div class="pmd-search-box">
                <input type="text" placeholder="Buscar notas... (em breve)" class="pmd-input" disabled>
            </div>
            <div class="pmd-empty-state">
                <p>A busca será implementada em uma próxima versão.</p>
            </div>
        </div>

        {{-- TAGS --}}
        <div x-show="railSection === 'tags'" class="pmd-sidebar-panel">
            <div class="pmd-panel-header">
                <span>Tags</span>
                <button class="pmd-icon-btn" @click="openCreateTag()" title="Nova tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </button>
            </div>
            <div class="pmd-tags-list">
                <template x-for="tag in allTags" :key="tag.id">
                    <div class="pmd-tag-item">
                        <span class="pmd-tag-preview" x-html="renderTagBadge(tag)"></span>
                        <span class="pmd-tag-name" x-text="tag.name"></span>
                        <div class="pmd-tag-actions">
                            <button class="pmd-icon-btn-sm" @click="openEditTag(tag)" title="Editar">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="pmd-icon-btn-sm pmd-danger" @click="deleteTag(tag)" title="Excluir">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="allTags.length === 0" class="pmd-empty-state">
                    <p>Nenhuma tag criada.</p>
                    <button class="pmd-btn-primary" @click="openCreateTag()">Nova tag</button>
                </div>
            </div>
        </div>

        {{-- SETTINGS --}}
        <div x-show="railSection === 'settings'" class="pmd-sidebar-panel">
            <div class="pmd-panel-header">Configurações</div>
            <div class="pmd-settings-section">
                <div class="pmd-settings-label">Abrir arquivos em:</div>
                <label class="pmd-radio-label">
                    <input type="radio" name="openIn" value="new-tab" x-model="openIn" @change="saveSettings()">
                    <span>Nova aba</span>
                </label>
                <label class="pmd-radio-label">
                    <input type="radio" name="openIn" value="current-tab" x-model="openIn" @change="saveSettings()">
                    <span>Substituir a aba atual</span>
                </label>
            </div>
            <div class="pmd-settings-section">
                <div class="pmd-settings-label">Aparência:</div>
                <label class="pmd-radio-label">
                    <input type="radio" name="theme" value="dark" :checked="darkMode" @change="darkMode = true; saveSettings()">
                    <span>Escuro</span>
                </label>
                <label class="pmd-radio-label">
                    <input type="radio" name="theme" value="light" :checked="!darkMode" @change="darkMode = false; saveSettings()">
                    <span>Claro</span>
                </label>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="pmd-main" :class="{ 'pmd-main--full': !sidebarOpen }">
        {{-- Sidebar toggle when hidden --}}
        <button x-show="!sidebarOpen" class="pmd-sidebar-toggle" @click="sidebarOpen = true" title="Abrir painel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9,18 15,12 9,6"/>
            </svg>
        </button>

        {{-- Tabs bar --}}
        <div class="pmd-tabs-bar" x-show="tabs.length > 0">
            <div class="pmd-tabs-scroll">
                <template x-for="tab in tabs" :key="tab.id">
                    <button class="pmd-tab" :class="{ 'pmd-tab--active': tab.id === activeTabId }"
                        @click="activateTab(tab.id)">
                        <span class="pmd-tab-name" x-text="tab.name"></span>
                        <span x-show="tab.isDirty" class="pmd-tab-dirty" title="Não salvo">●</span>
                        <span class="pmd-tab-close" @click.stop="closeTab(tab.id)" title="Fechar">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </span>
                    </button>
                </template>
            </div>
            <button class="pmd-tab-new" @click="createNote(selectedFolderId)" title="Nova nota">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        </div>

        {{-- Editor area --}}
        <div class="pmd-editor-area">
            {{-- Empty state --}}
            <div x-show="!activeTab" class="pmd-empty-editor">
                <div class="pmd-empty-editor-content">
                    <div class="pmd-empty-logo">post_md</div>
                    <p class="pmd-empty-desc">Selecione uma nota ou crie uma nova.</p>
                    <button class="pmd-btn-primary" @click="createNote(selectedFolderId)">Nova nota</button>
                </div>
            </div>

            {{-- Note editor --}}
            <div x-show="activeTab" class="pmd-note-wrapper" x-cloak>
                {{-- Note bar --}}
                <div class="pmd-note-bar">
                    <div class="pmd-note-path">
                        <span x-show="activeTab?.folderName" x-text="activeTab?.folderName" class="pmd-note-folder"></span>
                        <span x-show="activeTab?.folderName" class="pmd-note-sep">/</span>
                        <span x-text="activeTab?.name" class="pmd-note-filename"></span>
                    </div>
                    <div class="pmd-note-bar-actions">
                        <div class="pmd-view-toggle">
                            <button class="pmd-view-btn" :class="{ active: activeTab?.viewMode === 'reading' }"
                                @click="setViewMode('reading')">Leitura</button>
                            <button class="pmd-view-btn" :class="{ active: activeTab?.viewMode === 'source' }"
                                @click="setViewMode('source')">Fonte</button>
                        </div>
                        <button class="pmd-tags-btn" @click="toggleTagsPopover()" title="Gerenciar tags">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                <circle cx="7" cy="7" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Tags</span>
                            <span x-show="activeTab?.tags?.length > 0" x-text="activeTab?.tags?.length" class="pmd-tags-count"></span>
                        </button>
                    </div>
                </div>

                {{-- Tags popover --}}
                <div x-show="tagsPopoverOpen" @click.away="tagsPopoverOpen = false" class="pmd-tags-popover" x-cloak>
                    <div class="pmd-tags-popover-header">Tags desta nota</div>
                    <div class="pmd-tags-popover-list">
                        <template x-for="tag in allTags" :key="tag.id">
                            <label class="pmd-tag-checkbox">
                                <input type="checkbox"
                                    :checked="activeTab?.tags?.some(t => t.id === tag.id)"
                                    @change="toggleNoteTag(tag, $event.target.checked)">
                                <span class="pmd-tag-preview" x-html="renderTagBadge(tag)"></span>
                                <span x-text="tag.name"></span>
                            </label>
                        </template>
                        <div x-show="allTags.length === 0" class="pmd-empty-state-sm">Nenhuma tag criada.</div>
                    </div>
                    <div class="pmd-tags-popover-footer">
                        <button class="pmd-btn-sm" @click="openCreateTag(); tagsPopoverOpen = false">+ Nova tag</button>
                    </div>
                </div>

                {{-- Markdown toolbar (source mode) --}}
                <div x-show="activeTab?.viewMode === 'source'" class="pmd-md-toolbar">
                    <button class="pmd-md-btn" @click="insertMd('heading')" title="Título (H2)">H</button>
                    <div class="pmd-md-sep"></div>
                    <button class="pmd-md-btn pmd-md-btn--bold" @click="insertMd('bold')" title="Negrito">B</button>
                    <button class="pmd-md-btn pmd-md-btn--italic" @click="insertMd('italic')" title="Itálico">I</button>
                    <button class="pmd-md-btn pmd-md-btn--strike" @click="insertMd('strikethrough')" title="Tachado">S</button>
                    <div class="pmd-md-sep"></div>
                    <button class="pmd-md-btn" @click="insertMd('ul')" title="Lista">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                            <circle cx="3" cy="6" r="1.5" fill="currentColor"/><circle cx="3" cy="12" r="1.5" fill="currentColor"/><circle cx="3" cy="18" r="1.5" fill="currentColor"/>
                        </svg>
                    </button>
                    <button class="pmd-md-btn" @click="insertMd('ol')" title="Lista numerada">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/>
                            <path d="M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                        </svg>
                    </button>
                    <button class="pmd-md-btn" @click="insertMd('checklist')" title="Checklist">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,11 12,14 22,4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                        </svg>
                    </button>
                    <div class="pmd-md-sep"></div>
                    <button class="pmd-md-btn" @click="insertMd('blockquote')" title="Citação">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
                            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
                        </svg>
                    </button>
                    <button class="pmd-md-btn" @click="insertMd('link')" title="Link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                        </svg>
                    </button>
                    <div class="pmd-md-sep"></div>
                    <button class="pmd-md-btn pmd-md-btn--code" @click="insertMd('code')" title="Código inline">`</button>
                    <button class="pmd-md-btn" @click="insertMd('codeblock')" title="Bloco de código">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16,18 22,12 16,6"/><polyline points="8,6 2,12 8,18"/>
                        </svg>
                    </button>
                    <button class="pmd-md-btn" @click="insertMd('hr')" title="Linha horizontal">—</button>
                </div>

                {{-- Content area --}}
                <div class="pmd-content-area">
                    {{-- Reading mode --}}
                    <div x-show="activeTab?.viewMode === 'reading'" class="pmd-reading"
                        x-html="renderedContent" x-cloak></div>

                    {{-- Source mode --}}
                    <textarea x-show="activeTab?.viewMode === 'source'"
                        x-ref="editor"
                        class="pmd-editor"
                        x-model="editorContent"
                        @input="onEditorInput()"
                        @keydown.tab.prevent="insertTab()"
                        :placeholder="'Escreva em Markdown...'"
                        spellcheck="true"
                        x-cloak></textarea>
                </div>

                {{-- Status bar --}}
                <div class="pmd-status-bar">
                    <span class="pmd-save-status"
                        :class="{
                            'status-editing': activeTab?.saveStatus === 'editing',
                            'status-saving': activeTab?.saveStatus === 'saving',
                            'status-saved': activeTab?.saveStatus === 'saved',
                            'status-error': activeTab?.saveStatus === 'error'
                        }"
                        x-text="saveStatusText"></span>
                    <span class="pmd-status-sep" x-show="activeTab?.saveStatus === 'error'">·</span>
                    <button x-show="activeTab?.saveStatus === 'error'" class="pmd-retry-btn" @click="retrySave()">
                        Tentar novamente
                    </button>
                    <span class="pmd-status-info" x-show="activeTab?.viewMode === 'source'" x-text="cursorInfo"></span>
                </div>
            </div>
        </div>
    </main>

    {{-- Context menu --}}
    <div x-show="contextMenu.visible" x-cloak
        class="pmd-context-menu"
        :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px`"
        @click.away="contextMenu.visible = false">
        <template x-for="item in contextMenu.items" :key="item.label">
            <button class="pmd-context-item"
                :class="{ 'pmd-context-item--danger': item.danger }"
                @click="item.action(); contextMenu.visible = false"
                x-text="item.label">
            </button>
        </template>
    </div>

    {{-- Inline rename overlay --}}
    <div x-show="inlineEdit.visible" x-cloak class="pmd-inline-edit-overlay" @click.self="cancelInlineEdit()">
    </div>

    {{-- Modal: create/edit tag --}}
    <div x-show="tagModal.open" x-cloak class="pmd-modal-overlay" @click.self="tagModal.open = false">
        <div class="pmd-modal">
            <div class="pmd-modal-header">
                <span x-text="tagModal.isEdit ? 'Editar tag' : 'Nova tag'"></span>
                <button class="pmd-modal-close" @click="tagModal.open = false">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="pmd-modal-body">
                <div class="pmd-form-group">
                    <label class="pmd-label">Nome</label>
                    <input type="text" class="pmd-input" x-model="tagModal.name" placeholder="Nome da tag" @keydown.enter="saveTag()">
                </div>
                <div class="pmd-form-group">
                    <label class="pmd-label">Modo de exibição</label>
                    <div class="pmd-radio-group">
                        <label class="pmd-radio-label">
                            <input type="radio" value="color" x-model="tagModal.display_mode"> Cor
                        </label>
                        <label class="pmd-radio-label">
                            <input type="radio" value="emoji" x-model="tagModal.display_mode"> Emoji
                        </label>
                        <label class="pmd-radio-label">
                            <input type="radio" value="both" x-model="tagModal.display_mode"> Ambos
                        </label>
                    </div>
                </div>
                <div class="pmd-form-group" x-show="tagModal.display_mode === 'color' || tagModal.display_mode === 'both'">
                    <label class="pmd-label">Cor (ex: #E25C5C)</label>
                    <div class="pmd-color-input-row">
                        <input type="text" class="pmd-input pmd-input--sm" x-model="tagModal.color_hex" placeholder="#RRGGBB" maxlength="7">
                        <div class="pmd-color-swatch" :style="`background: ${tagModal.color_hex || '#888'}`"></div>
                    </div>
                </div>
                <div class="pmd-form-group" x-show="tagModal.display_mode === 'emoji' || tagModal.display_mode === 'both'">
                    <label class="pmd-label">Emoji</label>
                    <input type="text" class="pmd-input pmd-input--sm" x-model="tagModal.emoji" placeholder="Emoji" maxlength="10">
                    <div class="pmd-emoji-picker">
                        <template x-for="em in commonEmojis" :key="em">
                            <button class="pmd-emoji-btn" @click="tagModal.emoji = em" x-text="em"
                                :class="{ active: tagModal.emoji === em }"></button>
                        </template>
                    </div>
                </div>
                <div class="pmd-modal-preview">
                    <span class="pmd-label">Preview:</span>
                    <span x-html="renderTagBadge(tagModal)"></span>
                    <span x-text="tagModal.name || 'Nome da tag'" class="pmd-tag-name-preview"></span>
                </div>
                <div class="pmd-form-error" x-show="tagModal.error" x-text="tagModal.error"></div>
            </div>
            <div class="pmd-modal-footer">
                <button class="pmd-btn-secondary" @click="tagModal.open = false">Cancelar</button>
                <button class="pmd-btn-primary" @click="saveTag()">Salvar</button>
            </div>
        </div>
    </div>

    {{-- Modal: move item --}}
    <div x-show="moveModal.open" x-cloak class="pmd-modal-overlay" @click.self="moveModal.open = false">
        <div class="pmd-modal pmd-modal--sm">
            <div class="pmd-modal-header">
                <span>Mover para...</span>
                <button class="pmd-modal-close" @click="moveModal.open = false">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="pmd-modal-body">
                <div class="pmd-move-list">
                    <button class="pmd-move-item" @click="executeMove(null)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Raiz (sem pasta)</span>
                    </button>
                    <template x-for="folder in flatFolders" :key="folder.id">
                        <button class="pmd-move-item"
                            :disabled="moveModal.itemType === 'folder' && (folder.id === moveModal.itemId || folder._isDescendant)"
                            @click="executeMove(folder.id)"
                            :style="`padding-left: ${(folder._depth + 1) * 16 + 8}px`">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                            </svg>
                            <span x-text="folder.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm delete modal --}}
    <div x-show="confirmModal.open" x-cloak class="pmd-modal-overlay" @click.self="confirmModal.open = false">
        <div class="pmd-modal pmd-modal--sm">
            <div class="pmd-modal-header">Confirmar exclusão</div>
            <div class="pmd-modal-body">
                <p x-text="confirmModal.message"></p>
            </div>
            <div class="pmd-modal-footer">
                <button class="pmd-btn-secondary" @click="confirmModal.open = false">Cancelar</button>
                <button class="pmd-btn-danger" @click="confirmModal.action(); confirmModal.open = false">Excluir</button>
            </div>
        </div>
    </div>

    {{-- Inline edit input (positioned absolutely) --}}
    <input x-show="inlineEdit.visible" x-cloak
        type="text"
        class="pmd-inline-input"
        x-ref="inlineInput"
        x-model="inlineEdit.value"
        :style="`top: ${inlineEdit.y}px; left: ${inlineEdit.x}px; width: ${inlineEdit.w}px`"
        @keydown.enter.prevent="confirmInlineEdit()"
        @keydown.escape.prevent="cancelInlineEdit()"
        @blur="confirmInlineEdit()">
    <div x-show="inlineEdit.error" x-cloak class="pmd-inline-error"
        :style="`top: ${inlineEdit.y + 30}px; left: ${inlineEdit.x}px`"
        x-text="inlineEdit.error"></div>
</div>

{{-- Initial data --}}
<script>
    window.__WORKSPACE__ = {
        tree: @json($tree),
        tags: @json($tags),
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@endsection
