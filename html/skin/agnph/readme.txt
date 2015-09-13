This readme file is to document how to create a new skin.

Any base skin directory will be added to the theme selector for users. In this
folder, you may add or replace any template or css file in the base AGNPH skin
directory. These will be included and sourced properly when the skin is
selected. Any missing template/css files will be sourced from the base AGNPH
directory.

If a skin directory is removed, the template engine will fall back to the base
AGNPH directory (this must not be deleted!).

Note: skin directories must be lowercase.