// Matches the {name}/{company} substitution in App\Services\Seo\SeoTemplateFormatter.
// `code` is what actually gets inserted into the field; `label` is the short
// button text (shown as "+ label"); `description` is the tooltip — kept in
// plain language since non-technical admins use this field too.
//
// {name} resolves to the current page/item name where one exists, otherwise
// an empty string. {company} resolves to Setting::company, falling back to
// Meta Title (Client Panel) if that's blank — so inserting {company} into
// Meta Title (Client Panel) itself can render literally if Company is unset.
export const SEO_ITEM_SHORTCODES = [
    {
        code: '{name}',
        label: 'Title',
        description: "Adds this page's or group's own name here.",
    },
    {
        code: '{company}',
        label: 'Company',
        description: 'Adds your company name here.',
    },
]
