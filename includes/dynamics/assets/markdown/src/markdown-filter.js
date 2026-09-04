import MarkdownIt from 'markdown-it';

const markdown = new MarkdownIt({
    html: false,
    breaks: true,
    linkify: true,
    typographer: false,
});

const safeInlineTags = ['u', 'sub', 'sup', 'mark'];
let markerSequence = 0;

function preserveSafeInlineTags(source) {
    const markers = [];
    const markerRoot = `DYNAMICSMARKDOWN${Date.now()}${markerSequence++}`;
    const tagPattern = new RegExp(`<(${safeInlineTags.join('|')})>([\\s\\S]*?)<\\/\\1>`, 'gi');
    const markdownSource = source.replace(tagPattern, (match, tag, content) => {
        const index = markers.length;
        const normalizedTag = String(tag).toLowerCase();
        const open = `${markerRoot}OPEN${index}X`;
        const close = `${markerRoot}CLOSE${index}X`;
        markers.push({open, close, tag: normalizedTag});
        return `${open}${content}${close}`;
    });

    return {markdownSource, markers};
}

function restoreSafeInlineTags(html, markers) {
    return markers.reduce((output, marker) => output
        .split(marker.open).join(`<${marker.tag}>`)
        .split(marker.close).join(`</${marker.tag}>`), html);
}

function render(source) {
    const value = String(source ?? '');
    if (!value.trim()) {
        return '';
    }

    const protectedSource = preserveSafeInlineTags(value);
    return restoreSafeInlineTags(
        markdown.render(protectedSource.markdownSource),
        protectedSource.markers
    );
}

function renderInline(source) {
    const value = String(source ?? '');
    if (!value.trim()) {
        return '';
    }

    const protectedSource = preserveSafeInlineTags(value);
    return restoreSafeInlineTags(
        markdown.renderInline(protectedSource.markdownSource),
        protectedSource.markers
    );
}

window.DynamicsMarkdownFilter = Object.freeze({
    render,
    renderInline,
});

