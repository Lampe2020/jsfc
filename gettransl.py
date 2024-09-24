#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Translation extractor for chat.php
"""

import re, sys

with open((sys.argv[2] if len(sys.argv)>1 else 'chat.php'), 'r') as chat, open('chat_strings.php', 'w') as strings:
    strings.write('return [\n    ' + (' => [],\n    '.join((string if (not '(' in string) else f'// --POSSIBLY INCOMPLETE-- {string}') for string in re.compile(r'''translate\(([^\)]*)\)''').findall(chat.read()) if (string.startswith("'") or string.startswith('"')))) + ' => []\n];')
    print('Note: this string extractor is RegEx-based and extremely crude. So if any of the found strings contain a closing parenthesis `)` they\'ll be cut off in chat.strings and you\'ll need to manually find and extract them. Strings that contain opening parentheses `(` are marked by prepending them with "--POSSIBLY INCOMPLETE-- ".\nIncase translate.php cannot write to the file, set its group to www-data and its permissions to group-writeable.')
