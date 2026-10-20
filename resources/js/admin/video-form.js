/**
 * Add / edit video form. When a video file is chosen from the computer:
 *  - the source switches to "Upload from computer";
 *  - a video picked in the thumbnail box by mistake is moved to the video box;
 *  - a thumbnail is made from a frame of the video, unless one is already set.
 * The thumbnail can still be replaced with any image.
 */

/** Grab a frame (about 1 s in) from a local video file as a JPEG File. */
async function captureFrame(file, maxWidth = 1280) {
    const url = URL.createObjectURL(file);
    const video = document.createElement('video');
    video.muted = true;
    video.playsInline = true;
    video.preload = 'auto';

    const once = (event) =>
        new Promise((resolve, reject) => {
            const timer = setTimeout(() => reject(new Error('timeout')), 15000);
            video.addEventListener(event, () => (clearTimeout(timer), resolve()), { once: true });
            video.addEventListener('error', () => (clearTimeout(timer), reject(new Error('unsupported'))), { once: true });
        });

    try {
        video.src = url;
        await once('loadeddata');

        const at = Math.min(1, (video.duration || 0) / 3);
        if (at > 0) {
            const seeked = once('seeked');
            video.currentTime = at;
            await seeked;
        }

        const scale = Math.min(1, maxWidth / (video.videoWidth || maxWidth));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round((video.videoWidth || 1280) * scale);
        canvas.height = Math.round((video.videoHeight || 720) * scale);
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
        return blob ? new File([blob], `${file.name.replace(/\.[^.]+$/, '') || 'video'}-thumbnail.jpg`, { type: 'image/jpeg' }) : null;
    } finally {
        URL.revokeObjectURL(url);
        video.removeAttribute('src');
    }
}

function setFile(input, file) {
    const transfer = new DataTransfer();
    if (file) transfer.items.add(file);
    input.files = transfer.files;
}

export default ({ provider = 'youtube', hasThumbnail = false, maxBytes = 0 } = {}) => ({
    provider,
    hasThumbnail,
    status: '',
    working: false,
    automatic: false,

    get videoInput() {
        return this.$root.querySelector('input[name="file"]');
    },

    get thumbInput() {
        return this.$root.querySelector('input[name="thumbnail"]');
    },

    init() {
        this.thumbInput?.addEventListener('change', (event) => this.thumbnailChosen(event));
    },

    /** The admin picked something in the thumbnail box. */
    thumbnailChosen(event) {
        if (this.working) return;
        const [file] = event.target.files;
        if (!file) return;

        if (file.type.startsWith('video/')) {
            // A video in the thumbnail box: use it as the video instead.
            setFile(this.thumbInput, null);
            setFile(this.videoInput, file);
            this.videoChosen(file, true);
            return;
        }

        this.hasThumbnail = true;
        this.automatic = false;
        this.status = '';
    },

    /** A video was chosen in the video box (or moved there from the thumbnail box). */
    async videoChosen(file = this.videoInput?.files?.[0], forceThumbnail = false) {
        if (!file) return;
        this.provider = 'self_hosted';

        if (maxBytes && file.size > maxBytes) {
            setFile(this.videoInput, null);
            this.status = `This video is ${(file.size / 1048576).toFixed(0)} MB. The maximum is ${(maxBytes / 1048576).toFixed(0)} MB — please choose a smaller or compressed file.`;
            return;
        }

        if (this.hasThumbnail && !this.automatic && !forceThumbnail) {
            this.status = 'Video selected. Your own thumbnail is kept — use “Make from video” to replace it.';
            return;
        }

        await this.makeThumbnail(file);
    },

    async makeThumbnail(file = this.videoInput?.files?.[0]) {
        if (!file || !this.thumbInput) {
            this.status = 'Choose the video file first.';
            return;
        }

        this.status = 'Creating a thumbnail from the video…';
        try {
            const frame = await captureFrame(file);
            if (!frame) throw new Error('empty');

            this.working = true;
            setFile(this.thumbInput, frame);
            this.thumbInput.dispatchEvent(new Event('change', { bubbles: true }));
            this.working = false;

            this.hasThumbnail = true;
            this.automatic = true;
            this.status = 'Thumbnail created from the video. You can replace it with your own image.';
        } catch {
            this.working = false;
            this.status = 'A thumbnail could not be made from this video in your browser. You can upload one, or save without — it is optional.';
        }
    },
});
