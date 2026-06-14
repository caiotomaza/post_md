import { marked } from 'marked';
import DOMPurify from 'dompurify';

// Configure marked
marked.setOptions({
    gfm: true,
    breaks: true,
});

// Custom renderer for GFM task lists
const renderer = new marked.Renderer();
renderer.listitem = function(item) {
    if (item.task) {
        const checked = item.checked ? 'checked' : '';
        return `<li style="list-style:none"><input type="checkbox" ${checked} disabled> ${item.text}</li>`;
    }
    return `<li>${item.text}</li>`;
};
marked.use({ renderer });

// DOMPurify config: allow safe attributes and block dangerous URLs
const purifyConfig = {
    ALLOWED_TAGS: [
        'h1','h2','h3','h4','h5','h6',
        'p','br','hr',
        'strong','em','del','u','code','pre',
        'blockquote',
        'ul','ol','li','input',
        'a',
        'table','thead','tbody','tr','th','td',
        'img',
    ],
    ALLOWED_ATTR: [
        'href','src','alt','title',
        'class','style','id',
        'type','checked','disabled',
        'target','rel',
    ],
    FORBID_ATTR: ['onerror', 'onload', 'onclick'],
    ALLOW_DATA_ATTR: false,
    FORCE_BODY: false,
    HOOKS: {},
};

// Block dangerous URL schemes in href/src
DOMPurify.addHook('afterSanitizeAttributes', (node) => {
    if ('href' in node) {
        const href = node.getAttribute('href') || '';
        if (/^(javascript|vbscript|data):/i.test(href.trim())) {
            node.removeAttribute('href');
        } else if (href.startsWith('http://') || href.startsWith('https://')) {
            node.setAttribute('rel', 'noopener noreferrer');
            node.setAttribute('target', '_blank');
        }
    }
    if ('src' in node) {
        const src = node.getAttribute('src') || '';
        if (/^(javascript|vbscript|data):/i.test(src.trim())) {
            node.removeAttribute('src');
        }
    }
});

export function renderMarkdown(content) {
    if (!content) return '';
    const raw = marked.parse(content);
    return DOMPurify.sanitize(raw, purifyConfig);
}
