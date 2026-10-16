"""Require complete statement coverage in a PHPUnit Clover report."""

import sys
import xml.etree.ElementTree as ET

metrics = ET.parse(sys.argv[1]).find('project/metrics')
if metrics is None:
    raise SystemExit('Coverage report has no project metrics.')
statements = int(metrics.attrib['statements'])
covered = int(metrics.attrib['coveredstatements'])
if statements <= 0 or covered != statements:
    raise SystemExit(f'Statement coverage must be 100%: {covered}/{statements} covered.')
print(f'Statement coverage: 100% ({covered}/{statements}).')
