/**
 * Render an inline tag badge (dot + emoji based on display_mode).
 * Used in the tree and in tag lists.
 */
export function renderTagBadge(tag) {
    if (!tag || !tag.display_mode) return '';
    const esc = (s) => String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const dot = tag.color_hex
        ? `<span class="pmd-tag-dot" style="background:${esc(tag.color_hex)}" title="${esc(tag.name)}"></span>`
        : '';
    const emoji = tag.emoji
        ? `<span class="pmd-tag-emoji" title="${esc(tag.name)}">${esc(tag.emoji)}</span>`
        : '';

    switch (tag.display_mode) {
        case 'color': return dot;
        case 'emoji': return emoji;
        case 'both': return dot + emoji;
        default: return dot || emoji;
    }
}
