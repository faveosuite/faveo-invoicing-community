import { mount } from '@vue/test-utils'
import SimplePagination from '@/components/Reusable/SimplePagination.vue'

const mountPagination = (props = {}) =>
    mount(SimplePagination, { props })

describe('SimplePagination.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountPagination()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders Previous and Next buttons', () => {
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBe(2)
    })

    it('Previous button is disabled when prevPage is null', () => {
        const prevBtn = wrapper.findAll('button')[0]
        expect(prevBtn.attributes('disabled')).toBeDefined()
    })

    it('Next button is disabled when nextPage is null', () => {
        const nextBtn = wrapper.findAll('button')[1]
        expect(nextBtn.attributes('disabled')).toBeDefined()
    })

    it('Previous button is enabled when prevPage is provided', () => {
        wrapper = mountPagination({ prevPage: 'http://example.com?page=1' })
        const prevBtn = wrapper.findAll('button')[0]
        expect(prevBtn.attributes('disabled')).toBeUndefined()
    })

    it('Next button is enabled when nextPage is provided', () => {
        wrapper = mountPagination({ nextPage: 'http://example.com?page=2' })
        const nextBtn = wrapper.findAll('button')[1]
        expect(nextBtn.attributes('disabled')).toBeUndefined()
    })

    it('emits paginate with prev when Previous is clicked', async () => {
        wrapper = mountPagination({ prevPage: 'http://example.com?page=1' })
        await wrapper.findAll('button')[0].trigger('click')
        expect(wrapper.emitted('paginate')).toBeTruthy()
        expect(wrapper.emitted('paginate')[0]).toEqual(['prev'])
    })

    it('emits paginate with next when Next is clicked', async () => {
        wrapper = mountPagination({ nextPage: 'http://example.com?page=2' })
        await wrapper.findAll('button')[1].trigger('click')
        expect(wrapper.emitted('paginate')).toBeTruthy()
        expect(wrapper.emitted('paginate')[0]).toEqual(['next'])
    })

    it('renders Previous button text', () => {
        expect(wrapper.findAll('button')[0].text()).toContain('Previous')
    })

    it('renders Next button text', () => {
        expect(wrapper.findAll('button')[1].text()).toContain('Next')
    })

    it('both buttons have btn-primary class', () => {
        wrapper.findAll('button').forEach(btn => {
            expect(btn.classes()).toContain('btn-primary')
        })
    })
})
