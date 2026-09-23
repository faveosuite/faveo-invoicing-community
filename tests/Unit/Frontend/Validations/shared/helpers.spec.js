import { reqSelect } from '@/validations/shared/helpers'

describe('reqSelect', () => {
    const schema = reqSelect('Field is required')

    it('fails for null', async () => {
        await expect(schema.validate(null)).rejects.toThrow()
    })

    it('fails for undefined', async () => {
        await expect(schema.validate(undefined)).rejects.toThrow()
    })

    it('fails for empty string', async () => {
        await expect(schema.validate('')).rejects.toThrow()
    })

    it('fails for object with null id', async () => {
        await expect(schema.validate({ id: null, name: 'Foo' })).rejects.toThrow()
    })

    it('fails for object with empty-string id', async () => {
        await expect(schema.validate({ id: '', name: 'Foo' })).rejects.toThrow()
    })

    it('fails for object with whitespace-only id', async () => {
        await expect(schema.validate({ id: '   ', name: 'Foo' })).rejects.toThrow()
    })

    it('passes for object with numeric id', async () => {
        await expect(schema.validate({ id: 1, name: 'Foo' })).resolves.toBeTruthy()
    })

    it('passes for object with string id', async () => {
        await expect(schema.validate({ id: '42', name: 'Foo' })).resolves.toBeTruthy()
    })

    it('passes for a plain non-empty string', async () => {
        await expect(schema.validate('some-value')).resolves.toBeTruthy()
    })

    it('fails for a whitespace-only plain string', async () => {
        await expect(schema.validate('   ')).rejects.toThrow()
    })
})
