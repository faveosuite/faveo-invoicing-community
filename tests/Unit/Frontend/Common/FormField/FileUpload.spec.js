import { mount } from '@vue/test-utils'
import FileUpload from '@/components/Reusable/FormField/FileUpload.vue'

describe('FileUpload.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(FileUpload, {
            slots: { default: '<input type="file" class="test-input" />' },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the fileupload-wrapper container', () => {
        expect(wrapper.find('.fileupload-wrapper').exists()).toBe(true)
    })

    it('renders slotted content', () => {
        expect(wrapper.find('.test-input').exists()).toBe(true)
    })

    it('renders without slot content', () => {
        wrapper = mount(FileUpload)
        expect(wrapper.find('.fileupload-wrapper').exists()).toBe(true)
    })

    it('is a simple slot wrapper with no props', () => {
        expect(Object.keys(wrapper.props())).toHaveLength(0)
    })
})
