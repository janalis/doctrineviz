"""Prepare changelog entries for a versioned release without shell interpolation."""

import argparse
from datetime import datetime, timezone
from pathlib import Path
import re
import subprocess

VERSION = re.compile(r"(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\Z")


def version_tuple(version):
    if not VERSION.fullmatch(version):
        raise ValueError("Version must use X.Y.Z without a prefix or leading zeroes")
    return tuple(map(int, version.split(".")))


def resolve_version(requested, tags):
    versions = [tag[1:] for tag in tags if tag.startswith("v") and VERSION.fullmatch(tag[1:])]
    latest = max(map(version_tuple, versions), default=(0, 0, 0))
    if requested in ("patch", "minor", "major"):
        major, minor, patch = latest
        requested = {
            "patch": f"{major}.{minor}.{patch + 1}",
            "minor": f"{major}.{minor + 1}.0",
            "major": f"{major + 1}.0.0",
        }[requested]
    if version_tuple(requested) <= latest:
        raise ValueError("Release version must be newer than all existing versions")
    if requested in tags or f"v{requested}" in tags:
        raise ValueError("Release tag already exists")
    return requested


def prepare_changelog(text, version, date):
    version_tuple(version)
    marker = "## [Unreleased]\n"
    if text.count(marker) != 1:
        raise ValueError("Changelog must contain exactly one [Unreleased] section")
    before, remainder = text.split(marker, 1)
    match = re.search(r"^## \[", remainder, flags=re.MULTILINE)
    content = remainder[:match.start()] if match else remainder
    history = remainder[match.start():] if match else ""
    notes = content.strip()
    if not re.search(r"^- \S", notes, flags=re.MULTILINE):
        raise ValueError("Unreleased section must contain release notes")
    if re.search(rf"^## \[{re.escape(version)}\]", text, flags=re.MULTILINE):
        raise ValueError("Version already exists in changelog")
    updated = f"{before}{marker}\n## [{version}] - {date}\n\n{notes}\n\n{history}".rstrip() + "\n"
    return updated, notes + "\n"


def git(*args):
    return subprocess.check_output(["git", *args], text=True).strip()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("version", help="X.Y.Z, patch, minor or major")
    parser.add_argument("--notes", type=Path, required=True)
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()
    try:
        if git("status", "--porcelain"):
            raise ValueError("Release preparation requires a clean working tree")
        if git("branch", "--show-current") != "master":
            raise ValueError("Releases must be prepared on master")
        version = resolve_version(args.version, git("tag", "--list").splitlines())
        path = Path("CHANGELOG.md")
        updated, notes = prepare_changelog(path.read_text(), version, datetime.now(timezone.utc).date().isoformat())
        if not args.dry_run:
            path.write_text(updated)
        args.notes.write_text(notes)
        print(f"version={version}\ntag=v{version}")
    except (ValueError, OSError, subprocess.CalledProcessError) as error:
        parser.exit(1, f"Release preparation failed: {error}\n")


if __name__ == "__main__":
    main()
