# Releases

The canonical repository and Composer package are `janalis/doctrineviz`.
Releases are made from `master` using **Actions → release → Run workflow**.

## Prepare

Keep user-visible changes under `[Unreleased]` in `CHANGELOG.md`. Wait for CI
before starting a release. Enter `patch`, `minor`, `major` or an explicit
`X.Y.Z` version. Versions must increase and existing tags are rejected.

Run with **dry_run** enabled first. It validates the code, builds documentation
and prepares release notes without committing, tagging or publishing.

## Publish

Run the workflow again with dry-run disabled. It:

1. Runs CI, including 100% MSI and covered MSI.
2. Moves the unreleased changelog entries into a dated version section.
3. Commits with the GitHub Actions bot and atomically pushes the commit and annotated tag.
4. Explicitly dispatches CI for the tag and waits for all checks to pass on the generated release commit.
5. Creates a GitHub release using the changelog entries.
6. Checks Packagist for the tagged version and rebuilds GitHub Pages from the tag.

Preparation refuses a dirty checkout, duplicate version, empty changelog section
or stale `master`. Publishing is a separate job: retry failed publishing jobs
without cutting another version. Failed tag validation can also be retried independently.

GitHub does not run push workflows for commits pushed with `GITHUB_TOKEN`.
The separate `release-checks` workflow dispatches CI explicitly using the job's
scoped Actions permission; no personal access token is required. It can also be
run manually against an existing `vX.Y.Z` tag without publishing another release.

GitHub Actions is the active CI provider. Legacy Scrutinizer, StyleCI and Travis
webhooks are disabled; the Packagist webhook remains active.

## Packagist

The existing Packagist GitHub webhook discovers pushed tags. Composer derives
versions from tags; `composer.json` has no hard-coded version.

Optional repository secrets `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` enable an
explicit API refresh. Configure them through GitHub settings, never in files or
workflow inputs. The workflow does not print their values.

If a Packagist check fails, inspect webhook delivery and package status, then
retry the publishing job. Confirm a fresh Composer install resolves the release.

## GitHub Pages

Set the repository's Pages source to **GitHub Actions**. The docs workflow builds
VitePress and deploys to `https://janalis.github.io/doctrineviz/`. Pull requests
only build; pushes to `master`, manual dispatches and completed releases deploy.
