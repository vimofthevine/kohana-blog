# Getting Started

Most blog functionality will be functional simply by enabling the blog module
and can be accessed at your domain/blog.

To enable accurate tracking of daily and weekly article viewing statistics,
a cron job must be set up to access `/admin/blog/cron/stats_reset` daily.

To enable the daily email comment report, the config files, blog.php and email.php,
must have valid email settings.  A cron job must be set up to access
`/admin/blog/cron/comment_report` daily.

