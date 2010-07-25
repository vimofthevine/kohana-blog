<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Blog cron functions controller
 *
 * To run these cron methods, set up a cron job to run
 *
 * - `php index.php --uri=admin/blog/cron/stats_reset`
 * - `php index.php --uri=admin/blog/cron/comment_report`
 *
 * @package     Blog
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Blog_Cron extends Controller {

	/**
	 * Checks to make sure request environment is cli
	 */
	public function before() {
		if (Request::$protocol != 'cli')
		{
			throw new Kohana_Request_Exception('Attempt to access cron controller outside of command line environment',NULL,404);
		}
	}

	/**
	 * Reset daily statistics
	 */
	public function action_stats_reset() {
		$search   = Sprig::factory('blog_search');
		$articles = $search->search_by_state('published');

		foreach ($articles as $article)
		{
			$article->statistic->load()->reset()->update();
		}
	}

	/**
	 * Email daily comment report
	 */
	public function action_comment_report() {
		// Check if SwiftMailer installed
		if ( ! Kohana::find_file('vendor', 'swift/lib/swift_required'))
		{
			$this->request->response = 'Can not email daily comment report.  SwiftMailer is not installed.';
			return;
		}

		// Generate report
		$report = Request::factory('comments/blog-admin/report/86400')->execute()->response;

		try
		{
			// Include the SwiftMailer autoloader
			require_once Kohana::find_file('vendor', 'swift/lib/swift_required');

			// Create the message
			$message = Swift_Message::newInstance()
				->setContentType(Kohana::config('blog.comment_report.email_type'))
				->setSubject(Kohana::config('blog.comment_report.email_subject'))
				->setFrom(Kohana::config('blog.comment_report.email_from'))
				->setTo(Kohana::config('blog.comment_report.email_to'))
				->setBody($report);

			// Create the transport
			$transport = Swift_SmtpTransport::newInstance()
				->setHost(Kohana::config('email.options.hostname'))
				->setPort(Kohana::config('email.options.port'))
				->setEncryption(Kohana::config('email.options.encryption'))
				->setUsername(Kohana::config('email.options.username'))
				->setPassword(Kohana::config('email.options.password'));

			// Create the mailer
			$mailer = Swift_Mailer::newInstance($transport);

			// Send the message
			$mailer->send($message);

			$this->request->response = 'Daily comment report email sent.';
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Error occured sending daily comment report. '.$e->getMessage());
			$this->request->response = 'Error sending email report.'.PHP_EOL;
		}
	}

}	// End of Controller_Admin_Blog_Cron
