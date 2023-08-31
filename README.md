# Learning Goal Widget

<table>
  <tr>
    <td colspan="2">Learning Goal Widget</td>
  </tr>
  <tr>
    <td>Type</td>
    <td>Activity Module</td>
  </tr>
  <tr>
    <td>Plugins directory entry</td>
    <td>-</td>
  </tr>
  <tr>
    <td>Discussion</td>
    <td>-</td>
  </tr>
  <tr>
    <td>Maintainer(s)</td>
    <td>Alfred Wertner, Simone Franza</td>
  </tr>
</table>

This plugin allows lecturers to enter the learning goals and topics of a course/lecture.
The students can choose between the sunburst and the treemap view and use the 
plugin to track their learning progress on the different topics. 

Each topic contains one or multiple learning goals and the lecturer can 
provide external links to lecture or supplementary material. The 'taxonomy' 
(e.g. the list of topics and learning goals) can be entered manually via the settings 
or via a JSON file (see [JSON structure](#json-structure)).

*Treemap View with expanded colorscheme menu*

![colorschemes](https://github.com/simonefranza/moodle-mod_learninggoalwidget/assets/6499758/9877535d-416c-4776-9ad9-bf0f84019d8d)

*Sunburst View*

![sunburst](https://github.com/simonefranza/moodle-mod_learninggoalwidget/assets/6499758/3ed50661-8195-4605-8541-74afa4f82dfa)

### Table of contents

1. [Installation](#installation)
2. [Features](#features)
3. [JSON Structure](#json-structure)
4. [How to Report a Bug](#how-to-report-a-bug)
5. [License](#license)

## Installation

### ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

### Manually

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/amplifier

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Features

The users can move sliders to track their learning progress on each goal as 
well as open links to supplementary material or anything related to the topic/learning goal.

![slider](https://github.com/simonefranza/moodle-mod_learninggoalwidget/assets/6499758/48e8ad46-4d4f-4f69-8e3d-f60ae2cac163)

The treemap view allows the users to choose the preferred colorscheme, which is then 
saved in the local storage of the browser. Accessible color schemes suitable for 
individuals with color vision differences are provided. Furthermore, it is possible 
to change the font size. Finally, for individuals who rely on screen readers, 
the treemap view has been optimized to work with the Google Chrome
extension 
[Screen Reader](https://chrome.google.com/webstore/detail/screen-reader/kgejglhpjiefppelpmljglcjbhoiplfn).

## JSON Structure

Here is a starting template for the JSON taxonomy:

```javascript
{
  "name": "Learning Goal's Taxonomy",
  "children": [
    {
      "name": "First Topic",
      "keyword": "This is the description of the First Topic",
      "link": "https://www.example.com",
      "children": [
        {
          "name": "Learning Goal 1-1",
          "keyword": "This is the description of the First Learning Goal of the First Topic",
          "link": "https://www.example.com"
        },
        {
          "name": "Learning Goal 1-2",
          "keyword": "This is the description of the Second Learning Goal of the First Topic",
          "link": "https://www.example.com"
        }
      ]
    },
    {
      "name": "Second Topic",
      "keyword": "This is the description of the Second Topic",
      "link": "https://www.example.com",
      "children": [
        {
          "name": "Learning Goal 2-1",
          "keyword": "This is the description of the First Learning Goal of the Second Topic",
          "link": "https://www.example.com"
        },
        {
          "name": "Learning Goal 2-2",
          "keyword": "This is the description of the Second Learning Goal of the Second Topic",
          "link": "https://www.example.com"
        }
      ]
    }
  ]
}
```

The JSON object contains the two properties:

| Property | Type           | Description                           |
|----------|----------------|---------------------------------------|
| name     | string         | Name of the taxonomy (unused for now) |
| children | Array of Topic | List of topics of the taxonomy        |

The `Topic` object has the following structure:

| Property | Type           | Description                                 |
|----------|----------------|---------------------------------------------|
| name     | string         | Name of the Topic                           |
| keyword  | string         | Description of the Topic                    |
| link     | string         | Link to supplementary material, slides, etc |
| children | Array of Learning Goal | List of Learning Goals of this Topic|

The `Learning Goal` object has the following structure:

| Property | Type           | Description                                 |
|----------|----------------|---------------------------------------------|
| name     | string         | Name of the Learning Goal                   |
| keyword  | string         | Description of the Learning Goal            |
| link     | string         | Link to supplementary material, slides, etc |

## How to Report a Bug

Please, create an issue on the 
[Github repository](https://github.com/simonefranza/moodle-mod_learninggoalwidget/issues) 
of the plugin.

## License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
