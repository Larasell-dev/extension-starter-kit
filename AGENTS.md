# Conventions for Larasell extensions

## Workflow

- Start with a failing test. Run it, see it fail for the expected reason, then implement.
- Before committing, run `composer test`, `composer phpstan` and `composer lint`.
- Commit messages follow Conventional Commits without a scope (`feat: add wishlists`, not
  `feat(api): ...`). Commitlint checks this in CI.

## Domain code

- **Models** read related Larasell models from `ModelRegistry`, so apps that swap a model keep
  working: `$this->belongsTo(app(ModelRegistry::class)->product->class())`.
- **Copy what must outlive a link.** When a record points at something that can be deleted, such
  as a customer, store the values you show (e.g. their name) on the record and null the foreign
  key on delete.
- **Statuses are backed enums** with a `label()` method, cast on the model.
- **Actions** (`src/Actions`) hold every write: one class per verb with a `handle()` method, e.g.
  `SubmitProductReview`, `ApproveProductReview`. Controllers, admin tables, seeders and other
  code call actions instead of writing models directly.
- **Events** (`src/Events`) are dispatched by actions, one per change other modules may care about.
  They implement `ShouldDispatchAfterCommit` and carry the model as a public readonly property.
  Only dispatch when something actually changed, so listeners don't run twice.

## HTTP

- **Cruddy by design.** Controllers only have `index`, `show`, `create`, `store`, `edit`,
  `update` and `destroy`. A state change is its own resource, e.g. approving a review is
  `ProductReviewApprovalController@store`. `tests/Architecture/CruddyControllersTest.php`
  enforces this.
- **Admin pages** are registered in the service provider. Tables extend
  `Larasell\Admin\Tables\Table` and are added with `Navigation::index()`; nest them under the
  built-in item they belong to with `->under(NavItem::...)`. Other admin routes go in
  `AdminPanel::resolve()->routes(...)` so they get the panel's authentication.
- **APIs** follow JSON:API 1.1 with Laravel's `JsonApiResource`:
  - request bodies are JSON:API documents (`data.attributes`, `data.relationships`);
  - related records are relationships, not `*_id` attributes;
  - errors are JSON:API error objects with a `source.pointer` or `source.header`;
  - lists paginate with `page[number]` and `page[size]`, with a default and a maximum size;
  - updates use `PATCH`;
  - make the route middleware configurable, and rate limit endpoints that create records.
  - Describe the API in `openapi.yaml` and keep it in step with the responses.

## Tests

- Feature tests run against Testbench with the Larasell and admin providers; see
  `tests/TestCase.php`. `signInAdmin()` in `tests/Pest.php` signs in an admin.
- Assert observable results: database state, responses, dispatched events (`Event::fake()`).
- Use Pest snapshots (`toMatchSnapshot()`) for API responses. Freeze time with `travelTo()` so
  timestamps don't change, and read every new or changed snapshot before keeping it.
- Build URLs with `route()` rather than hard-coding them.

## Playground

`vendor/bin/testbench serve` serves an admin panel with the extension installed
(`testbench.yaml`). It runs with `APP_ENV=local`, so `/admin/__dev/login` signs you in as an
admin. Put sample data in `workbench/database/seeders/DatabaseSeeder.php`, created through the
extension's actions.

`larasell-dev/admin` comes from `dev-main` because its latest release lacks features extensions
use (`Navigation::index()`, badge columns, row actions). `dev-main` has no built assets, so
`.agents/setup` builds them in `vendor/larasell-dev/admin`. Switch to a version constraint once
a release has them.
