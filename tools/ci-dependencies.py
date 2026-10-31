"""Constrain directly required Symfony components for one CI matrix job.

The standalone components viz requires at 8.x (filesystem, process) stay as they are: an
application on Symfony 7.4 installs them beside its framework.
"""
import json
from pathlib import Path
import sys

version = sys.argv[1]
if version not in ("7.4.*", "8.0.*", "8.1.*"):
    raise SystemExit("Unsupported Symfony test series")
path = Path("composer.json")
data = json.loads(path.read_text())
for section in ("require", "require-dev"):
    for package in data[section]:
        if package.startswith("symfony/") and package not in ("symfony/filesystem", "symfony/process"):
            data[section][package] = version
path.write_text(json.dumps(data, indent=4) + "\n")
