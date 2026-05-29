const TOOLTIP_CLASS = 'has-tooltip'
const TOOLTIP_ATTR  = 'data-bs-title'

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
                label: 'Tooltip description',
                placeholder: 'Enter the tooltip text...',
            }],
        },
        buttons: [
            { type: 'cancel', text: 'Cancel' },
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
        tooltip: 'Add or edit a tooltip on selected text',
        onAction() {
            const node = getTooltipNode(editor)

            if (node) {
                openTooltipDialog(editor, {
                    title: 'Edit Tooltip',
                    submitLabel: 'Update',
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
                    text: 'Select the text you want to add a tooltip to first.',
                    type: 'warning',
                    timeout: 3000,
                })
                return
            }

            openTooltipDialog(editor, {
                title: 'Add Tooltip',
                submitLabel: 'Insert',
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
    plugins,
    toolbar:       toolbarItems.join(' | '),
    content_style: `.${TOOLTIP_CLASS} { text-decoration: underline dotted #aaa; cursor: pointer; }`,
    setup:         registerTooltipButton,
}
