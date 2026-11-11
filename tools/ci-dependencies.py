"""Constrain directly required Symfony components to one series for a CI matrix job.

composer.json is the single source of truth: each component lists one `^X.Y[.Z]` alternative
per supported series, and the job keeps that alternative's floor while capping it to the
series (`^8.1.11` becomes `~8.1.11`), so a `--prefer-lowest` run installs the declared floor.
A series that a component does not declare is an error.

The standalone components viz requires at 8.x (filesystem, process) stay as they are: an
application on Symfony 7.4 installs them beside its framework.
"""
import json
from pathlib import Path
import re
import sys

series = sys.argv[1]
if not re.fullmatch(r"\d+\.\d+", series):
    raise SystemExit(f"Expected a Symfony series such as 8.1, got {series!r}")
path = Path("composer.json")
data = json.loads(path.read_text())
for section in ("require", "require-dev"):
    for package, constraint in data[section].items():
        if not package.startswith("symfony/") or package in ("symfony/filesystem", "symfony/process"):
            continue
        floors = [
            match.group(1)
            for alternative in constraint.split("||")
            if (match := re.fullmatch(r"\^(" + re.escape(series) + r"(?:\.\d+)?)", alternative.strip()))
        ]
        if len(floors) != 1:
            raise SystemExit(f"{package} {constraint!r} does not declare Symfony {series} once")
        floor = floors[0]
        data[section][package] = "~" + (floor if floor.count(".") == 2 else floor + ".0")
path.write_text(json.dumps(data, indent=4) + "\n")
