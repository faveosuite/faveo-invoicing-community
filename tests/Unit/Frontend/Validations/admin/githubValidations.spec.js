import { githubSchema } from '@/validations/admin/githubValidations'

describe('githubSchema', () => {
    const valid = { git_username: 'octocat', git_password: 'ghp_secret' }

    it('passes with valid data', async () => {
        await expect(githubSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when git_username is empty', async () => {
        await expect(githubSchema.validate({ ...valid, git_username: '' })).rejects.toThrow()
    })

    it('fails when git_username is missing', async () => {
        const { git_username: _o, ...rest } = valid
        await expect(githubSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when git_username is only whitespace', async () => {
        await expect(githubSchema.validate({ ...valid, git_username: '   ' })).rejects.toThrow()
    })

    it('fails when git_password is empty', async () => {
        await expect(githubSchema.validate({ ...valid, git_password: '' })).rejects.toThrow()
    })

    it('fails when git_password is missing', async () => {
        const { git_password: _o, ...rest } = valid
        await expect(githubSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when git_password is only whitespace', async () => {
        await expect(githubSchema.validate({ ...valid, git_password: '   ' })).rejects.toThrow()
    })
})
