<?PHP

require_once('View.php');

class BlogView extends View
{
	public function fetch()
	{
		$slug = $this->request->get('slug', 'string');
		if (!empty($slug)) {
			return $this->fetch_article($slug);
		} else {
			return false;
		}
	}

	private function fetch_article($slug)
	{
    $article = $this->blog->get_article_by_slug($slug);
		if (!$article || !$article->published) {
      return false;
    }

    $this->design->assign('article', $article);

		return $this->design->fetch('article.tpl');
	}
}
