import { cloudTrialSchema } from '@/validations/client/cloudTrialValidations'

describe('cloudTrialSchema', () => {
    const valid = {
        domain:             'my-cloud',
        selectedProduct:    { id: 1, name: 'Product A' },
        selectedDataCenter: { id: 2, name: 'US East' },
    }

    it('passes with valid data', async () => {
        await expect(cloudTrialSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when domain is empty', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, domain: '' })).rejects.toThrow()
    })

    it('fails when domain is missing', async () => {
        const { domain: _o, ...rest } = valid // NOSONAR
        await expect(cloudTrialSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when domain starts with a hyphen', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, domain: '-invalid' })).rejects.toThrow()
    })

    it('fails when domain ends with a hyphen', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, domain: 'invalid-' })).rejects.toThrow()
    })

    it('fails when domain contains spaces', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, domain: 'my domain' })).rejects.toThrow()
    })

    it('passes when domain is alphanumeric with internal hyphens', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, domain: 'my-cloud-app' })).resolves.toBeTruthy()
    })

    it('fails when selectedProduct is null', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, selectedProduct: null })).rejects.toThrow()
    })

    it('fails when selectedProduct is empty string', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, selectedProduct: '' })).rejects.toThrow()
    })

    it('fails when selectedDataCenter is null', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, selectedDataCenter: null })).rejects.toThrow()
    })

    it('fails when selectedDataCenter has object with null id', async () => {
        await expect(cloudTrialSchema.validate({ ...valid, selectedDataCenter: { id: null } })).rejects.toThrow()
    })
})
