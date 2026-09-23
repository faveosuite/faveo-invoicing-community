import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import SeoFieldsCard from '@/components/Reusable/FormField/SeoFieldsCard.vue'

const onChangeMock = jest.fn()

// Reactive — the real pages always pass a reactive() form, and SeoFieldsCard's
// own watch(() => props.form.meta_title, ...) needs that to detect mutations.
const baseForm = () => reactive({
    meta_title: '',
    meta_description: '',
    og_title: '',
    og_description: '',
})

const mountCard = (props = {}) =>
    mount(SeoFieldsCard, {
        props: {
            form: baseForm(),
            onChange: onChangeMock,
            componentName: 'seo-fields-card-test',
            ...props,
        },
        global: {
            stubs: ['SeoMetaField', 'ImageUpload', 'Checkbox'],
        },
    })

describe('SeoFieldsCard.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountCard()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the outer card chrome by default (bare=false)', () => {
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('skips the outer card chrome when bare=true', () => {
        wrapper = mountCard({ bare: true })
        expect(wrapper.find('.card-header').exists()).toBe(false)
    })

    it('renders four SeoMetaField instances (title/description/og_title/og_description)', () => {
        expect(wrapper.findAllComponents({ name: 'SeoMetaField' }).length).toBe(4)
    })

    it('renders a Checkbox for "same as meta"', () => {
        expect(wrapper.findComponent({ name: 'Checkbox' }).exists()).toBe(true)
    })

    it('renders an ImageUpload for the OG image', () => {
        expect(wrapper.findComponent({ name: 'ImageUpload' }).exists()).toBe(true)
    })

    it('emits update:ogSameAsMeta when the checkbox handler fires', () => {
        wrapper.vm.onOgSameAsMetaChange(true)
        expect(wrapper.emitted('update:ogSameAsMeta')).toEqual([[true]])
    })

    it('mirrors meta_title/meta_description into og_title/og_description when same-as-meta is turned on', () => {
        wrapper = mountCard({
            form: { ...baseForm(), meta_title: 'My Title', meta_description: 'My Description' },
        })
        wrapper.vm.onOgSameAsMetaChange(true)
        expect(onChangeMock).toHaveBeenCalledWith('My Title', 'og_title')
        expect(onChangeMock).toHaveBeenCalledWith('My Description', 'og_description')
    })

    it('does not mirror fields when same-as-meta is turned off', () => {
        wrapper.vm.onOgSameAsMetaChange(false)
        expect(onChangeMock).not.toHaveBeenCalled()
        expect(wrapper.emitted('update:ogSameAsMeta')).toEqual([[false]])
    })

    it('keeps og_title mirrored to meta_title while same-as-meta is on and meta_title changes', async () => {
        const form = baseForm()
        wrapper = mountCard({ form, ogSameAsMeta: true })
        form.meta_title = 'Updated Title'
        await wrapper.vm.$nextTick()
        expect(onChangeMock).toHaveBeenCalledWith('Updated Title', 'og_title')
    })

    it('keeps og_description mirrored to meta_description while same-as-meta is on and meta_description changes', async () => {
        const form = baseForm()
        wrapper = mountCard({ form, ogSameAsMeta: true })
        form.meta_description = 'Updated Description'
        await wrapper.vm.$nextTick()
        expect(onChangeMock).toHaveBeenCalledWith('Updated Description', 'og_description')
    })

    it('does not mirror meta_title changes when same-as-meta is off', async () => {
        const form = baseForm()
        wrapper = mountCard({ form, ogSameAsMeta: false })
        form.meta_title = 'Updated Title'
        await wrapper.vm.$nextTick()
        expect(onChangeMock).not.toHaveBeenCalledWith('Updated Title', 'og_title')
    })

    it('emits image-change when the image handler fires', () => {
        const payload = { image: 'preview-url', file: new File([], 'og.png'), name: 'og.png' }
        wrapper.vm.onImageChange(payload)
        expect(wrapper.emitted('image-change')).toEqual([[payload]])
    })

    it('disables og_title/og_description fields when same-as-meta is on', () => {
        wrapper = mountCard({ ogSameAsMeta: true })
        const ogTitleField = wrapper.findAllComponents({ name: 'SeoMetaField' }).find(c => c.props('name') === 'og_title')
        expect(ogTitleField.props('disabled')).toBe(true)
    })

    it('leaves meta_title/meta_description fields enabled regardless of same-as-meta', () => {
        wrapper = mountCard({ ogSameAsMeta: true })
        const metaTitleField = wrapper.findAllComponents({ name: 'SeoMetaField' }).find(c => c.props('name') === 'meta_title')
        expect(metaTitleField.props('disabled')).toBeFalsy()
    })
})
