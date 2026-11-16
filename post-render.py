#!/usr/bin/env python3
"""Quarto post-render: leert den von Quarto in jede Listing-Sektion eingefügten, versteckten
Platzhalter «No matching items». Das Element bleibt bestehen (Quarto-JS blendet es beim Filtern
ein), zeigt aber keinen Text mehr."""
import os
import pathlib

out = pathlib.Path(os.environ.get("QUARTO_PROJECT_OUTPUT_DIR", "_site"))
needle = ">No matching items<"
for f in out.rglob("*.html"):
    s = f.read_text(encoding="utf-8")
    if needle in s:
        f.write_text(s.replace(needle, "><"), encoding="utf-8")
