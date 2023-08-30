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
    <td>[Simone Franza](https://github.com/simonefranza)</td>
  </tr>
</table>

This plugin allows lecturers to enter the learning goals and topics of a course/lecture.
The students can choose between the sunburst and the treemap view and use the 
plugin to track their learning progress on the different topics. 

Each topic contains one or multiple learning goals and the lecturer can 
provide external links to lecture or supplementary material. The 'taxonomy' 
(e.g. the list of topics and learning goals) can be entered manually via the settings 
or via a JSON file (see [JSON structure](#json-structure)).

Picture with colorschemes
*Treemap View*

Picture
*Sunburst View*

### Table of contents

1. [Features](#features)
2. [JSON Structure](#json-structure)
3. [How to Report a Bug](#how-to-report-a-bug)

## Features

The users can move sliders to track their learning progress on each goal as 
well as open links to supplementary material or anything related to the topic/learning goal.

Picture with slider selection

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
