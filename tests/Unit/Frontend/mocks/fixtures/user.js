export const userFixture = {
    id: 1,
    first_name: 'John',
    last_name: 'Doe',
    user_name: 'johndoe',
    email: 'john@example.com',
    role: 'admin',
    timezone: { name: 'UTC' },
    avatar: null,
}

export const clientUserFixture = {
    ...userFixture,
    id: 2,
    first_name: 'Jane',
    email: 'jane@example.com',
    role: 'user',
}
