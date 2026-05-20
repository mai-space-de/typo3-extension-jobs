# mai_jobs — Feature Reference

> Canonical feature and architecture reference for the `mai_jobs` TYPO3 extension.
> See `README.md` for installation and QA commands.

---

## 1. Job Record

Structured vacancy record stored in `tx_maijobs_job`.

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `title` | `varchar(255)` | yes | Backend list label |
| `description` | `text` (RTE) | yes | Full job description; rich text |
| `requirements` | `text` (RTE) | no | Skills / qualifications; rich text |
| `deadline` | `int` (Unix timestamp) | no | Application deadline; `0` = no deadline |
| `status` | `varchar(20)` | yes | Enum: `open` \| `filled` \| `closed`; default `open` |
| `categories` | relation (`sys_category`) | no | Zero or more TYPO3 system categories |

`Job::isOpen()` returns `true` when `status === 'open'`; the detail template uses this to
show or hide the application form.

Record order defaults to `sorting ASC` (drag-and-drop sortable in the backend).

---

## 2. Application Record

Submitted application stored in `tx_maijobs_application`.

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `first_name` | `varchar(255)` | yes | Applicant given name |
| `last_name` | `varchar(255)` | yes | Applicant family name; backend list label |
| `email` | `varchar(255)` | yes | Email address |
| `message` | `text` | no | Cover letter / free-text message |
| `cv` | FAL relation | no | Max 1 file; allowed: `pdf,doc,docx,odt` |
| `status` | `varchar(20)` | yes | Enum: `pending` \| `reviewed` \| `accepted` \| `rejected`; default `pending` |
| `submitted_at` | `int` (Unix timestamp) | yes | Set by `confirmAction`; read-only in backend |
| `job` | relation → `tx_maijobs_job` | no | Parent vacancy; set by `confirmAction` |

Backend list ordering defaults to `submitted_at DESC` (newest first).

---

## 3. sys_category Integration

Job records use TYPO3's built-in `sys_category` system — no custom category table
(`tx_maijobs_category` does **not** exist). Category relations are stored in
`sys_category_record_mm`.

This is consistent with the project-wide architecture rule: `mai_news`, `mai_gallery`,
`mai_faq`, `mai_testimonials`, and `mai_timeline` all share the same `sys_category` tree.
Never add a custom category table to this extension.

The `JobController` reads `settings.categoryUids` from FlexForm and queries
`sys_category` directly via `ConnectionPool` to resolve category titles for the
client-side filter.

---

## 4. Content Element Plugins

| CType | Plugin identifier | Controller | Action | Backend group |
| --- | --- | --- | --- | --- |
| `maispace_jobs_list` | `tx_maijobs_list` | `JobController` | `list` | `maispace_feature` |
| `maispace_jobs_detail` | `tx_maijobs_detail` | `JobController` + `ApplicationController` | `detail`, `apply`, `confirm` | `maispace_feature` |

Both CTypes use the shared `mai-content` icon (registered by `mai_base`).

The list plugin has a FlexForm attached (`JobsPlugin.xml`).
The detail plugin has no FlexForm (it renders the job determined by the URL argument).

---

## 5. Frontend Rendering

### `listAction`

Seven query branches, resolved in priority order:

| Condition | Method called |
| --- | --- |
| `pages` set + `categoryUid > 0` + `statusFilter` set | `findFromPagesByCategoryAndStatus($pages, $categoryUid, $statusFilter)` |
| `pages` set + `categoryUid > 0` | `findFromPagesByCategory($pages, $categoryUid)` |
| `pages` set + `statusFilter` set | `findFromPagesByStatus($pages, $statusFilter)` |
| `pages` set only | `findFromPages($pages)` |
| `categoryUid > 0` only | `findByCategoryUid($categoryUid)` |
| `statusFilter` set only | `findByStatus($statusFilter)` |
| none set | `findAll()` |

Variables assigned to the list template:

| Variable | Type | Notes |
| --- | --- | --- |
| `jobs` | `QueryResultInterface` | Ordered by `sorting ASC` |
| `categories` | `array[]` | Each entry: `['uid' => int, 'title' => string]` |
| `activeCategoryUid` | `int` | `0` when no category filter active |
| `statusFilter` | `string` | Empty string when no status filter active |
| `settings` | `array` | All FlexForm + TypoScript settings |

### `detailAction`

Variables assigned to the detail template:

| Variable | Type | Notes |
| --- | --- | --- |
| `job` | `Job` | Single vacancy record |
| `settings` | `array` | All TypoScript settings |

The application form section is rendered inline in `Detail.html` and is only shown when
`job.isOpen` is true.

### `applyAction`

Shows the application form. Called when a visitor clicks "Apply Now".

| Variable | Type | Notes |
| --- | --- | --- |
| `job` | `Job` | The target vacancy |
| `application` | `Application` | New (empty) or re-populated after validation failure |
| `settings` | `array` | TypoScript settings |

### `confirmAction`

Persists the submitted application and shows the confirmation screen.
Sets `submittedAt` from the current timestamp (`Context::date.timestamp`) and associates
`job` before calling `applicationRepository->add()`.

| Variable | Type | Notes |
| --- | --- | --- |
| `job` | `Job` | The target vacancy |
| `application` | `Application` | The persisted application |
| `settings` | `array` | TypoScript settings |

---

## 6. Client-side Category Filter

The list template emits HTML data attributes that a lightweight JavaScript module
reads to show/hide job cards without a page reload.

| Attribute | Element | Value | Purpose |
| --- | --- | --- | --- |
| `data-jobs-filter="all"` | filter button | literal `"all"` | Reset — show all cards |
| `data-jobs-filter="{category.uid}"` | filter button | numeric category UID | Show only cards in this category |
| `data-jobs-item="1"` | `<article>` card | `"1"` | Marks a job card as filterable |
| `data-jobs-categories="1,5,12"` | `<article>` card | comma-separated category UIDs | Categories the job belongs to |

The active filter button receives the CSS class `mai-jobs__filter--active`. No JavaScript
library is bundled — the consumer theme is responsible for wiring up the filter logic
against these data attributes.

---

## 7. Application Form

The inline application form is rendered inside the `ApplicationForm` section of
`Detail.html` and is only visible when `job.isOpen` is true. It uses Extbase's
`<f:form>` ViewHelper with `objectName="application"`.

Form fields:

| Field | Input type | Required | Extbase property |
| --- | --- | --- | --- |
| First Name | `text` | yes | `application.firstName` |
| Last Name | `text` | yes | `application.lastName` |
| Email Address | `email` | yes | `application.email` |
| Cover Letter | `textarea` | no | `application.message` |

CV file upload is intentionally omitted from the frontend form in the current
implementation. The `cv` FAL relation exists on the `Application` model and TCA but
is not exposed via the Fluid `<f:form>` — it is available for backend-driven
submissions only.

The form POSTs to `ApplicationController::confirmAction`, which persists the record
and renders the confirmation screen (`Application/Confirm.html`).

---

## 8. FlexForm Configuration

FlexForm `JobsPlugin.xml` is attached to the `maispace_jobs_list` CType only.

| Field | Type | Default | Notes |
| --- | --- | --- | --- |
| `settings.pages` | group (pages, max 20) | — | Storage page(s) for job records |
| `settings.categoryUids` | category (manyToMany) | — | Categories for the client-side filter nav |
| `settings.statusFilter` | select single | `` (all) | Pre-filter by status: `open`, `filled`, `closed` |
| `settings.detailPid` | group (pages, max 1) | — | Page UID of the detail plugin; required for "Apply Now" links |

`settings.categoryUid` (singular) is the runtime variable used by the controller;
`settings.categoryUids` (plural) from FlexForm is not directly consumed by the query
methods (the controller reads `settings.categoryUid` from TypoScript defaults instead).
When a category filter is required, set `settings.categoryUid` in TypoScript constants.

---

## 9. TypoScript Configuration

### Constants

```typoscript
plugin.tx_maijobs {
    view {
        templateRootPath = EXT:mai_jobs/Resources/Private/Templates/
        partialRootPath  = EXT:mai_jobs/Resources/Private/Partials/
        layoutRootPath   = EXT:mai_jobs/Resources/Private/Layouts/
    }
}
plugin.tx_maijobs_list {
    view {
        templateRootPath =   # override: leave empty to use tx_maijobs base path
        partialRootPath  =
        layoutRootPath   =
    }
    persistence.storagePid =   # storage page UID for job records
}
plugin.tx_maijobs_detail {
    view {
        templateRootPath =
        partialRootPath  =
        layoutRootPath   =
    }
    persistence.storagePid =
}
```

### Setup

Both plugins resolve view paths using a two-level override chain:
priority `10` (plugin-specific override) falls back to priority `0` (shared base path).
The `tx_maijobs` base constants always point to the bundled template directory;
the per-plugin constants are empty by default, so the extension templates are used
unless a project sets its own override path.

---

## 10. JobPosting Schema.org Contract

This section documents the intended mapping between `Job` model fields and the
[schema.org `JobPosting`](https://schema.org/JobPosting) vocabulary. Structured
data output (JSON-LD) is **not yet rendered** by the bundled templates; this
contract defines what a compliant implementation must produce.

### Property mapping

| schema.org property | Source | Notes |
| --- | --- | --- |
| `@context` | literal | `"https://schema.org"` |
| `@type` | literal | `"JobPosting"` |
| `title` | `job.title` | Plain text; no HTML |
| `description` | `job.description` | Strip HTML tags before output |
| `responsibilities` | `job.requirements` | Strip HTML tags; omit when empty |
| `validThrough` | `job.deadline` | ISO 8601 date (`Y-m-d`); omit when `deadline === 0` |
| `employmentType` | `job.categories` | Optional; map category titles to schema.org values: `FULL_TIME`, `PART_TIME`, `VOLUNTEER`, `INTERNSHIP`, `CONTRACTOR`, `TEMPORARY` |
| `datePosted` | `crdate` | ISO 8601 date from the record creation timestamp |
| `url` | detail page URL | Absolute canonical URL of the detail page |
| `hiringOrganization` | site configuration | `Organization` sub-object; `name` and `url` from site settings |
| `jobLocation` | _not in model_ | Omit until a `location` field is added to the `Job` model |

### Output format

The JSON-LD block must be injected into `<head>` via
`PageRenderer::addHeaderData()` or an equivalent TYPO3 API call. It must not be
placed inline in the body template.

### Status values

`job.status` does not map directly to a schema.org property. Use the
[`DirectApply`](https://schema.org/directApply) flag when `status === 'open'`, and
omit `directApply` (or set it to `false`) for `filled` and `closed` jobs.

The full JSON-LD block for an open job with a deadline looks like:

```json
{
  "@context": "https://schema.org",
  "@type": "JobPosting",
  "title": "Ehrenamtlicher Gruppenleiter",
  "description": "Wir suchen …",
  "responsibilities": "Grundkenntnisse in …",
  "validThrough": "2026-08-31",
  "datePosted": "2026-05-01",
  "directApply": true,
  "url": "https://www.bgm-pulheim.org/jobs/ehrenamtlicher-gruppenleiter",
  "hiringOrganization": {
    "@type": "Organization",
    "name": "BGM Pulheim",
    "url": "https://www.bgm-pulheim.org"
  }
}
```

---

## 11. Database Tables

### `tx_maijobs_job`

| Column | Type | Default | Notes |
| --- | --- | --- | --- |
| `uid` | `int` PK | auto | Standard TYPO3 system column |
| `pid` | `int` | `0` | Storage page UID |
| `tstamp` | `int` | `0` | Last modification timestamp |
| `crdate` | `int` | `0` | Creation timestamp; used for `datePosted` schema.org mapping |
| `deleted` | `tinyint(1)` | `0` | Soft-delete flag |
| `hidden` | `tinyint(1)` | `0` | Visibility flag |
| `sorting` | `int` | `0` | Manual sort order |
| `title` | `varchar(255)` | `''` | |
| `description` | `text` | `NULL` | Rich text |
| `requirements` | `text` | `NULL` | Rich text |
| `deadline` | `int unsigned` | `0` | Unix timestamp; `0` = no deadline |
| `status` | `varchar(20)` | `'open'` | `open` \| `filled` \| `closed` |
| `categories` | `int unsigned` | `0` | Count of category relations; resolved via `sys_category_record_mm` |

### `tx_maijobs_application`

| Column | Type | Default | Notes |
| --- | --- | --- | --- |
| `uid` | `int` PK | auto | Standard TYPO3 system column |
| `pid` | `int` | `0` | Storage page UID |
| `tstamp` | `int` | `0` | Last modification timestamp |
| `crdate` | `int` | `0` | Creation timestamp |
| `deleted` | `tinyint(1)` | `0` | Soft-delete flag |
| `hidden` | `tinyint(1)` | `0` | Visibility flag |
| `first_name` | `varchar(255)` | `''` | |
| `last_name` | `varchar(255)` | `''` | Backend list label |
| `email` | `varchar(255)` | `''` | |
| `message` | `text` | `NULL` | Cover letter |
| `cv` | `int unsigned` | `0` | FAL reference count; resolved via `sys_file_reference` |
| `status` | `varchar(20)` | `'pending'` | `pending` \| `reviewed` \| `accepted` \| `rejected` |
| `submitted_at` | `int unsigned` | `0` | Unix timestamp set by `confirmAction` |
| `job` | `int unsigned` | `0` | FK → `tx_maijobs_job.uid` |

---

## 12. Architecture Constraints

- **No custom category table.** Jobs use `sys_category` and `sys_category_record_mm`.
  Never create `tx_maijobs_category`.
- **No mail dispatch inside this extension.** Application submission notifications must
  be routed through `mai_mail` (`MailService::queue()`). Never add `symfony/mailer`
  or direct mail logic to `mai_jobs`.
- **No SCSS.** All styling lives in `mai_assets` / `mai_theme`. CSS class names follow
  the BEM convention documented in the template files.
- **FAL for files.** The `cv` relation uses TYPO3 File Abstraction Layer
  (`sys_file_reference`). Never store file paths as plain strings.
- **Detail page separation.** The list and detail plugins must be placed on different
  pages. `settings.detailPid` on the list plugin drives the "Apply Now" link target.
  Without it, the list renders job cards with no navigation link.
- **Schema.org output is pending.** The `JobPosting` field mapping in section 10 is a
  specification contract. Until JSON-LD output is implemented in the template or a
  dedicated DataProcessor, the structured data is not emitted.
