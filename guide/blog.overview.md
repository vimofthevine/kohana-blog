# Blog Overview

The blog module is an extension to the kohana-admin module and provides
controllers for managing categories, tags, articles, and comments.

A front-end controller is provided for:

 - Displaying lists of articles, filterable by date, category, or tag
 - Displaying individual articles
 - Handling comments on individual articles

An internal controller is provided for:

 - Getting recently posted articles
 - Getting the most popular articles of the week
 - Getting recently posted comments

A cron method is provided to email a daily report of new comments made in
the past 24 hours.

