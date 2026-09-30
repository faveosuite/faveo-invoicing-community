import http from '@/plugins/axios'

const TOOLTIP_CLASS = 'has-tooltip'
const TOOLTIP_ATTR  = 'data-bs-title'

// Backs the toolbar's "image" button — without this, TinyMCE's image dialog
// only accepts a pasted external URL, no local file upload. Same shape as
// favMer's customImageUploadHandler (blobInfo, progress) -> Promise<location>,
// built on this project's shared `http` client instead of a raw XHR so it
// keeps the axios instance's CSRF header + 401/419 session handling.
function uploadEditorImage(blobInfo, progress) {
    return new Promise((resolve, reject) => {
        const fd = new FormData()
        fd.append('file', blobInfo.blob(), blobInfo.filename())

        http.post('/editor-image-upload', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (e) => {
                if (progress && e.total) progress((e.loaded / e.total) * 100)
            },
        })
            .then(res => {
                const location = res.data?.location
                if (location) resolve(location)
                else reject(__('message.image_upload_failed'))
            })
            .catch(err => {
                const data = err.response?.data
                reject(data?.errors?.file?.[0] || data?.message || __('message.image_upload_failed'))
            })
    })
}

function getTooltipNode(editor) {
    return editor.dom.getParent(editor.selection.getStart(), `.${TOOLTIP_CLASS}`)
}

function openTooltipDialog(editor, { title, submitLabel, initialValue = '', onConfirm }) {
    editor.windowManager.open({
        title,
        initialData: { tooltipText: initialValue },
        body: {
            type: 'panel',
            items: [{
                type: 'input',
                name: 'tooltipText',
                label: __('message.tooltip_description'),
                placeholder: __('message.enter_tooltip_text'),
            }],
        },
        buttons: [
            { type: 'cancel', text: __('message.cancel') },
            { type: 'submit', text: submitLabel, primary: true },
        ],
        onSubmit(api) {
            const { tooltipText } = api.getData()
            if (!tooltipText.trim()) return
            onConfirm(tooltipText)
            api.close()
        },
    })
}

function registerTooltipButton(editor) {
    editor.ui.registry.addButton('addtooltip', {
        text: 'Tooltip',
        icon: 'info',
        tooltip: __('message.add_edit_tooltip_hint'),
        onAction() {
            const node = getTooltipNode(editor)

            if (node) {
                openTooltipDialog(editor, {
                    title: __('message.edit_tooltip'),
                    submitLabel: __('message.update'),
                    initialValue: node.getAttribute(TOOLTIP_ATTR) ?? '',
                    onConfirm(text) {
                        editor.dom.setAttrib(node, TOOLTIP_ATTR, text)
                        editor.fire('change')
                    },
                })
                return
            }

            const selected = editor.selection.getContent({ format: 'text' }).trim()

            if (!selected) {
                editor.notificationManager.open({
                    text: __('message.select_text_for_tooltip'),
                    type: 'warning',
                    timeout: 3000,
                })
                return
            }

            openTooltipDialog(editor, {
                title: __('message.add_tooltip'),
                submitLabel: __('message.insert'),
                onConfirm(text) {
                    editor.selection.setContent(
                        `<span class="${TOOLTIP_CLASS}" data-bs-toggle="tooltip" ${TOOLTIP_ATTR}="${text}">${selected}</span>`
                    )
                },
            })
        },
    })
}

const toolbarItems = [
    'undo redo',
    'blocks',
    'bold italic underline',
    'forecolor',
    'alignleft aligncenter alignright',
    'bullist numlist',
    'link image',
    'table',
    'code fullscreen',
    'addtooltip',
]

const plugins = [
    'preview', 'searchreplace', 'autolink', 'autosave', 'save', 'directionality', 'code',
    'visualblocks', 'visualchars', 'image', 'link', 'media', 'codesample', 'table', 'charmap',
    'pagebreak', 'nonbreaking', 'insertdatetime', 'advlist', 'lists', 'fullscreen', 'wordcount',
    'help', 'emoticons',
]

export const editorInit = {
    promotion:     false,
    branding:      false,
    // Drops the bottom bar entirely (element path + the "Press Alt+0 for
    // help" accessibility hint) — not something editors here act on.
    statusbar:     false,
    plugins,
    toolbar:       toolbarItems.join(' | '),
    content_style: `.${TOOLTIP_CLASS} { text-decoration: underline dotted #aaa; cursor: pointer; }`,
    setup:         registerTooltipButton,
    images_upload_handler: uploadEditorImage,
    // TinyMCE's default (true) rewrites inserted URLs relative to the admin
    // editor's own page — which then resolves to the wrong path wherever the
    // saved HTML is rendered on a different URL (e.g. the client-facing page).
    // Keep URLs absolute as entered so they resolve identically everywhere.
    relative_urls: false,
}
