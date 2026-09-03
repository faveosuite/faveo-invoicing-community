import { mount } from '@vue/test-utils'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const onChangeMock = jest.fn()

const mountTextField = (props = {}) =>
    mount(TextField, {
        props: {
            name: 'username',
            value: '',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                FormFieldTemplate: {
                    template: '<div class="form-field-template-stub"><slot /></div>',
                    props: ['label', 'labelStyle', 'name', 'classname', 'hint', 'required', 'showNewButton', 'newBtnName', 'onClickEvent', 'inputGroupBtn', 'error'],
                },
            },
        },
    })

describe('TextField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountTextField()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders FormFieldTemplate stub', () => {
        expect(wrapper.find('.form-field-template-stub').exists()).toBe(true)
    })

    it('renders a text input by default', () => {
        expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('renders textarea when type is textarea', () => {
        wrapper = mountTextField({ type: 'textarea' })
        expect(wrapper.find('textarea').exists()).toBe(true)
    })

    it('renders password input when type is password', () => {
        wrapper = mountTextField({ type: 'password' })
        expect(wrapper.find('input[type="password"]').exists()).toBe(true)
    })

    it('renders toggle password button for password type', () => {
        wrapper = mountTextField({ type: 'password' })
        expect(wrapper.find('button.btn-secondary').exists()).toBe(true)
    })

    it('toggles password visibility when eye button clicked', async () => {
        wrapper = mountTextField({ type: 'password' })
        const toggleBtn = wrapper.find('button.btn-secondary')
        await toggleBtn.trigger('click')
        expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('hides password again when eye button clicked twice', async () => {
        wrapper = mountTextField({ type: 'password' })
        const toggleBtn = wrapper.find('button.btn-secondary')
        await toggleBtn.trigger('click')
        await toggleBtn.trigger('click')
        expect(wrapper.find('input[type="password"]').exists()).toBe(true)
    })

    it('calls onChange when input value changes', async () => {
        await wrapper.find('input').setValue('new value')
        expect(onChangeMock).toHaveBeenCalledWith('new value', 'username')
    })

    it('calls onChange when textarea value changes', async () => {
        wrapper = mountTextField({ type: 'textarea' })
        await wrapper.find('textarea').setValue('text content')
        expect(onChangeMock).toHaveBeenCalledWith('text content', 'username')
    })

    it('sets value from prop', () => {
        wrapper = mountTextField({ value: 'initial' })
        expect(wrapper.find('input').element.value).toBe('initial')
    })

    it('syncs value when prop changes', async () => {
        await wrapper.setProps({ value: 'updated' })
        expect(wrapper.vm.changedValue).toBe('updated')
    })

    it('applies is-invalid when error is set', () => {
        wrapper = mountTextField({ error: 'Required' })
        expect(wrapper.find('input').classes()).toContain('is-invalid')
    })

    it('does not apply is-invalid when no error', () => {
        expect(wrapper.find('input').classes()).not.toContain('is-invalid')
    })

    it('disables input when disabled is true', () => {
        wrapper = mountTextField({ disabled: true })
        expect(wrapper.find('input').attributes('disabled')).toBeDefined()
    })

    it('sets readonly when readonly is true', () => {
        wrapper = mountTextField({ readonly: true })
        expect(wrapper.find('input').attributes('readonly')).toBeDefined()
    })

    it('applies custom inputClass', () => {
        wrapper = mountTextField({ inputClass: 'my-input-class' })
        expect(wrapper.find('input').classes()).toContain('my-input-class')
    })

    it('renders placeholder from placeholder prop', () => {
        wrapper = mountTextField({ placeholder: 'Enter username' })
        expect(wrapper.find('input').attributes('placeholder')).toBe('Enter username')
    })

    it('renders placeholder from placehold prop as fallback', () => {
        wrapper = mountTextField({ placehold: 'Type here' })
        expect(wrapper.find('input').attributes('placeholder')).toBe('Type here')
    })

    it('renders email type input', () => {
        wrapper = mountTextField({ type: 'email' })
        expect(wrapper.find('input[type="email"]').exists()).toBe(true)
    })

    it('sets maxlength from max prop', () => {
        wrapper = mountTextField({ max: 100 })
        expect(wrapper.find('input').attributes('maxlength')).toBe('100')
    })

    it('renders required attribute when required is true', () => {
        wrapper = mountTextField({ required: true })
        expect(wrapper.find('input').attributes('required')).toBeDefined()
    })
})
