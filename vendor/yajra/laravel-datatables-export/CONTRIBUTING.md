# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/yajra/laravel-datatables-export).


## Commit messages — Conventional Commits (recommended)

We follow the Conventional Commits specification for all commit messages to keep history clear and to allow automated changelog/release tooling.

- Format: `<type>(optional-scope): <short-description>`
- Examples:
  - `feat: add export queue support`
  - `fix(export): handle empty dataset exception`
  - `chore(deps): bump phpunit to ^10.0`
  - `docs: update README with contrib guidelines`

Types we commonly use: `feat`, `fix`, `chore`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`.

This is a documentation-only guideline. There is no required tooling in the repository to enforce commit messages; contributors are asked to follow the format when writing commit messages. If you prefer to use local tooling (for example `commitlint` + `husky`) that's fine, but it's not required by the maintainers.

If you run into issues or have questions about commit formatting, open an issue or drop a note in the PR — maintainers can help.


## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.


**Happy coding**!
