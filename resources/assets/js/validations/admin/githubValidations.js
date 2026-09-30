import * as yup from 'yup'

export const githubSchema = yup.object({
    git_username: yup.string().trim().required(() => __('message.enter_github_username')),
    git_password: yup.string().trim().required(() => __('message.enter_github_password')),
})
