export const datetimeSettingsFixture = {
    timezone: 'UTC',
    dateFormat: 'd/m/Y',
    timeFormat: 'H:i',
}

export const paginationFixture = {
    current_page: 1,
    last_page: 3,
    per_page: 10,
    total: 25,
    from: 1,
    to: 10,
}

export const validationErrorFixture = (fields = {}) => ({
    message: 'The given data was invalid.',
    errors: Object.fromEntries(
        Object.entries(fields).map(([k, v]) => [k, [v]])
    ),
})
