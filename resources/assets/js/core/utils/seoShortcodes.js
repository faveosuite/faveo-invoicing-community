// Matches the {name}/{company}/{title} substitution in App\Services\Seo\SeoTemplateFormatter.
// `code` is what actually gets inserted into the field; `label` is the short
// button text (shown as "+ label"); `description` is the tooltip — kept in
// plain language since non-technical admins use this field too.
//
// {name} resolves to the current page/item name where one exists, otherwise
// an empty string. {company} resolves to Setting::company, falling back to
// Meta Title (Client Panel) if that's blank — so inserting {company} into
// Meta Title (Client Panel) itself can render literally if Company is unset.
// {title} resolves to Setting::title (Settings > Company > Title — your
// app/brand name, shown next to the logo in the admin sidebar).
export const SEO_ITEM_SHORTCODES = [
    {
        code: '{name}',
        label: 'Name',
        description: "Adds this page's or group's own name here.",
    },
    {
        code: '{company}',
        label: 'Company',
        description: 'Adds your company name here.',
    },
    {
        code: '{title}',
        label: 'Title',
        description: 'Adds your application/brand name (Settings > Company > Title) here.',
    },
]
