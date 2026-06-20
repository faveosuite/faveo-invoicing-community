import { connectionSchema, listSchema } from '@/validations/admin/mailchimpValidations'

describe('connectionSchema', () => {
    it('passes with a valid apiKey', async () => {
        await expect(connectionSchema.validate({ apiKey: 'abc123xyz' })).resolves.toBeTruthy()
    })

    it('fails when apiKey is empty', async () => {
        await expect(connectionSchema.validate({ apiKey: '' })).rejects.toThrow()
    })

    it('fails when apiKey is missing', async () => {
        await expect(connectionSchema.validate({})).rejects.toThrow()
    })

    it('fails when apiKey is only whitespace', async () => {
        await expect(connectionSchema.validate({ apiKey: '   ' })).rejects.toThrow()
    })
})

describe('listSchema', () => {
    const valid = { listId: 'abc123', subscribeStatus: 'subscribed' }

    it('passes with valid data', async () => {
        await expect(listSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when listId is missing', async () => {
        const { listId: _o, ...rest } = valid
        await expect(listSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when subscribeStatus is empty', async () => {
        await expect(listSchema.validate({ ...valid, subscribeStatus: '' })).rejects.toThrow()
    })

    it('fails when subscribeStatus is missing', async () => {
        const { subscribeStatus: _o, ...rest } = valid
        await expect(listSchema.validate(rest)).rejects.toThrow()
    })
})
