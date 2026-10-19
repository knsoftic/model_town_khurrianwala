/**
 * Minimal rich-text editor (bold, italic, headings, lists, links, quote).
 * The HTML is cleaned again on the server by App\Services\HtmlSanitizer.
 */
export default () => ({
    html: '',

    init() {
        const textarea = this.$refs.input;
        this.$refs.editor.innerHTML = textarea.value;
        this.html = textarea.value;
    },

    sync() {
        this.html = this.$refs.editor.innerHTML;
        this.$refs.input.value = this.html;
    },

    exec(command, value = null) {
        this.$refs.editor.focus();
        document.execCommand(command, false, value);
        this.sync();
    },

    block(tag) {
        this.exec('formatBlock', tag);
    },

    link() {
        const url = prompt('Link URL (https://… or /page):');
        if (url === null) return;
        if (url === '') {
            this.exec('unlink');
            return;
        }
        if (!/^(https?:\/\/|\/|mailto:|tel:)/i.test(url)) {
            alert('Links must start with https://, http://, /, mailto: or tel:');
            return;
        }
        this.exec('createLink', url);
    },

    paste(event) {
        event.preventDefault();
        const text = event.clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
        this.sync();
    },
});
