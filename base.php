<?php
/**
 * @package  Akismet
 * @author   Alan Hardman <alan@phpizza.com>
 * @version  1.0.0
 */

namespace Plugin\Akismet;

class Base extends \Plugin {

	/**
	 * Initialize the plugin, adding authentication hooks and routes
	 */
	public function _load() {
		$f3 = \Base::instance();
        $this->_hook("model/issue.before_save", array($this, "issueBeforeSave"));
        $this->_hook("model/issue/comment.before_save", array($this, "commentBeforeSave"));
	}

	/**
	 * Check if plugin is installed
	 * @return bool
	 */
	public function _installed() {
		return !!\Base::instance()->get("site.plugins.akismet.api_key");
	}

	/**
	 * Generate page for admin panel
	 */
	public function _admin() {
		$f3 = \Base::instance();
		if ($f3->get("POST.api_key")) {
			$helper = Helper::instance();
			if ($helper->isKeyValid($f3->get("POST.api_key"))) {
				\Model\Config::setVal("site.plugins.akismet.api_key", $f3->get("POST.api_key"));
			} else {
				$f3->set("error", "Invalid API key.");
				$f3->set("POST.api_key", null);
			}
		}
		echo \Helper\View::instance()->render("akismet/view/admin.html");
	}

	/**
	 * Check for spam before saving issues
	 *
	 * @param  \Model\Issue $issue
	 * @return \Model\Issue
	 */
	public function issueBeforeSave(\Model\Issue $issue) {
		if ($issue->changed("description")) {
			$helper = Helper::instance();
			if ($helper->isSpam($issue->description, "issue")) {
				$f3 = \Base::instance();
				$log = new \Log("akismet.log");
				$log->write("Issue blocked: " . $f3->get("IP") . " - " . $f3->get("AGENT"));
				throw new \Exception("Spam detected, not saving.");
			}
		}
		return $issue;
	}

	/**
	 * Check for spam before saving issues
	 *
	 * @param  \Model\Issue\Comment $comment
	 * @return \Model\Issue\Comment
	 */
	public function commentBeforeSave(\Model\Issue\Comment $comment) {
		$helper = Helper::instance();
		if ($helper->isSpam($comment->text, "issue")) {
			$f3 = \Base::instance();
			$log = new \Log("akismet.log");
			$log->write("Comment blocked: " . $f3->get("IP") . " - " . $f3->get("AGENT"));
			throw new \Exception("Spam detected, not saving.");
		}
		return $comment;
	}

}
