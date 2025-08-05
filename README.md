# Dixeo Course Generator Plugin for Moodle

## Overview

The Dixeo Course Generator plugin allows Moodle administrators and course creators to generate courses using AI. By providing a course description and optional files, the plugin communicates with an external AI service to generate a fully functional course in Moodle.

## Features

- **AI-Powered Course Generation**: Generate courses based on a description.
- **File Upload Support**: Upload files to be used as source material.
- **Automatic LTI Module Setup**: Integrates the generated content via LTI.
- **Multi-language Support**: Available in English, French, Spanish, and Italian.

## Installation

1. **Download** the plugin and place the `dixeo_coursegen` folder into your Moodle's `blocks` directory.

2. **Visit** the Site Administration page to complete the installation process.

## Configuration

1. **Navigate** to `Site Administration > Plugins > Blocks > Dixeo Course Generator`.

2. **Enter** the API key provided by Edunao.

3. **Specify** the category name where generated courses will be created.

4. **Ensure** that the LTI enrolment method is enabled on your Moodle platform.

## Usage

1. **Add** an instance of the block `Dixeo Course Generator` to your dashboard.

2. **Provide** a course description in the text area.

3. **(Optional)** Upload any files you want to include as source material.

4. **Click** on the **Generate** button.

5. **Wait** for the course to be generated. A success message will appear with a link to the new course.

## Permissions

- Users must have the `moodle/course:create` capability to access and use the plugin.

## Support

For support and further information, please contact Edunao or refer to the plugin documentation.
