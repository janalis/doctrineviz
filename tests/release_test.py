import importlib.util
from pathlib import Path
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]
SPEC = importlib.util.spec_from_file_location("release", ROOT / "tools/release.py")
release = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(release)


class ReleaseTest(unittest.TestCase):
    def test_version_bumps_and_explicit_version(self):
        tags = ["v0.3.1", "v0.2.8", "not-a-version", "v1.0.0-rc1"]
        for request, expected in [("patch", "0.3.2"), ("minor", "0.4.0"), ("major", "1.0.0"), ("0.4.0", "0.4.0")]:
            with self.subTest(request=request):
                self.assertEqual(expected, release.resolve_version(request, tags))
        self.assertEqual("0.0.1", release.resolve_version("patch", []))
        self.assertEqual("1.0.1", release.resolve_version("patch", ["v0.9.9", "v1.0.0"]))

    def test_rejects_invalid_existing_and_older_versions(self):
        for version in ["v0.4.0", "0.04.0", "0.4", "0.4.0-rc1", "0.4.0\n", "$(echo unsafe)", "0.3.0", "0.3.1"]:
            with self.subTest(version=version), self.assertRaises(ValueError):
                release.resolve_version(version, ["v0.3.1"])
        with self.assertRaises(ValueError):
            release.resolve_version("0.4.0", ["0.4.0"])

    def test_changelog_and_notes(self):
        text = "# Changelog\n\n## [Unreleased]\n\n### Added\n\n- New renderer.\n\n## [0.3.1] - 2017-11-30\n\n- Fix.\n"
        updated, notes = release.prepare_changelog(text, "0.4.0", "2026-10-16")
        self.assertEqual("### Added\n\n- New renderer.\n", notes)
        self.assertEqual(text.replace("## [Unreleased]\n", "## [Unreleased]\n\n## [0.4.0] - 2026-10-16\n"), updated)
        with self.assertRaises(ValueError):
            release.prepare_changelog(updated, "0.4.1", "2026-10-16")
        with self.assertRaises(ValueError):
            release.prepare_changelog(text, "0.3.1", "2026-10-16")
        self.assertTrue(release.prepare_changelog("## [Unreleased]\n\n- First release.\n", "0.1.0", "2026-10-16")[0].endswith("- First release.\n"))

    def test_missing_empty_and_ambiguous_changelog(self):
        for text in ["", "## [Unreleased]\n", "## [Unreleased]\n\n### Added\n", "## [Unreleased]\n## [Unreleased]\n- Ambiguous.\n"]:
            with self.subTest(text=text), self.assertRaises(ValueError):
                release.prepare_changelog(text, "0.4.0", "2026-10-16")

    def test_cli_dry_run_and_preparation(self):
        with tempfile.TemporaryDirectory(prefix="doctrineviz-release-") as directory:
            path = Path(directory)
            def git(*args):
                return subprocess.run(["git", *args], cwd=path, check=True, capture_output=True, text=True)
            git("init", "--initial-branch=master")
            git("config", "user.name", "Release tests")
            git("config", "user.email", "release-tests@example.invalid")
            changelog = path / "CHANGELOG.md"
            text = "# Changelog\n\n## [Unreleased]\n\n- Synthetic change.\n"
            changelog.write_text(text)
            git("add", "CHANGELOG.md")
            git("commit", "-m", "Fixture")
            git("tag", "v0.3.1")
            # The notes artifact must be outside the release checkout.
            notes = ROOT / "var/release-test-notes.md"
            notes.parent.mkdir(exist_ok=True)
            command = ["python3", str(ROOT / "tools/release.py"), "minor", "--notes", str(notes)]
            dry_run = subprocess.run(command + ["--dry-run"], cwd=path, capture_output=True, text=True)
            self.assertEqual(0, dry_run.returncode, dry_run.stderr)
            self.assertEqual("version=0.4.0\ntag=v0.4.0\n", dry_run.stdout)
            self.assertEqual(text, changelog.read_text())
            self.assertEqual("- Synthetic change.\n", notes.read_text())
            git("switch", "-c", "feature")
            wrong_branch = subprocess.run(command, cwd=path, capture_output=True, text=True)
            self.assertEqual(1, wrong_branch.returncode)
            self.assertIn("master", wrong_branch.stderr)
            git("switch", "master")
            result = subprocess.run(command, cwd=path, capture_output=True, text=True)
            self.assertEqual(0, result.returncode, result.stderr)
            self.assertIn("## [0.4.0]", changelog.read_text())
            dirty = subprocess.run(command, cwd=path, capture_output=True, text=True)
            self.assertEqual(1, dirty.returncode)
            self.assertIn("clean working tree", dirty.stderr)
            notes.unlink()


if __name__ == "__main__":
    unittest.main()
