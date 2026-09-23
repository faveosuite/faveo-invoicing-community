import { msg91Schema } from '@/validations/admin/msg91Validations'

describe('msg91Schema', () => {
    const valid = {
        msg91_auth_key:    'auth-key-abc',
        msg91_sender:      'MYAPP',
        msg91_template_id: 'tpl-123',
    }

    it('passes with valid data', async () => {
        await expect(msg91Schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when msg91_auth_key is empty', async () => {
        await expect(msg91Schema.validate({ ...valid, msg91_auth_key: '' })).rejects.toThrow()
    })

    it('fails when msg91_auth_key is missing', async () => {
        const { msg91_auth_key: _o, ...rest } = valid // NOSONAR
        await expect(msg91Schema.validate(rest)).rejects.toThrow()
    })

    it('fails when msg91_auth_key is only whitespace', async () => {
        await expect(msg91Schema.validate({ ...valid, msg91_auth_key: '   ' })).rejects.toThrow()
    })

    it('fails when msg91_sender is empty', async () => {
        await expect(msg91Schema.validate({ ...valid, msg91_sender: '' })).rejects.toThrow()
    })

    it('fails when msg91_template_id is empty', async () => {
        await expect(msg91Schema.validate({ ...valid, msg91_template_id: '' })).rejects.toThrow()
    })

    it('fails when msg91_template_id is missing', async () => {
        const { msg91_template_id: _o, ...rest } = valid // NOSONAR
        await expect(msg91Schema.validate(rest)).rejects.toThrow()
    })
})
