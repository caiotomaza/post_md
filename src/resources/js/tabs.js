const STORAGE_KEY = 'pmd_tabs';

export function persistTabs(tabs, activeTabId) {
    try {
        const activeTab = tabs.find(t => t.id === activeTabId);
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            tabs: tabs.map(t => ({ noteId: t.noteId, name: t.name })),
            activeNoteId: activeTab ? activeTab.noteId : null,
        }));
    } catch (e) {
        // storage quota exceeded or unavailable
    }
}

export function restoreTabs() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
}

export function buildTabs(notes) {
    return notes.map(note => ({
        id: `tab-${Date.now()}-${Math.random().toString(36).slice(2)}`,
        noteId: note.id,
        name: note.name,
        content: note.content || '',
        folderName: note.folder_name || null,
        tags: note.tags || [],
        isDirty: false,
        saveStatus: 'saved',
        viewMode: 'source',
    }));
}
