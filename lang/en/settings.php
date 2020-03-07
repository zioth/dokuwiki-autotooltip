<?php
$lang['style'] = 'A space-delimited set of default classes. "plugin-autotooltip__" will be prepended to ' .
	'each one. Included options are "default," "plain" and "small"';
$lang['delay'] = 'Time in miliseconds to wait before showing a tooltip';
$lang['linkall_inclusions'] = 'When using the renderer plugin to add tooltips to all links, this is a regular expression for pages or namespaces to include. For example, "^wiki:|^stuff:" inludes only links from the wiki and stuff namespaces. Leave blank to include all pages.';
$lang['linkall_exclusions'] = 'A regular expression for pages or namespaces to exclude. When combined with linkall_inclusions, this means "Include these pages, except those pages"';
$lang['linkall_points_to'] = 'A regular expression for pages or namespaces that links must be pointing towards. If a link does not match this expression, the tooltip will not be generated. For example, "^:wiki:|^[.]" would show tooltips on any links in an included/not-excluded page that links to the ":wiki" namespace or any relative page respectively';
