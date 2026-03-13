<?php

require_once 'Simpla.php';

class Articles extends Simpla
{
    public function get_articles()
    {
      $query = $this->db->placehold("SELECT * FROM __articles");
      $this->db->query($query);

      return $this->db->results();
    }

    public function get_article($id)
    {
      $query = $this->db->placehold("SELECT * FROM __articles WHERE id = ?", (int) $id);
      $this->db->query($query);

      return $this->db->result();
    }

    public function new_article($slug, $title, $description, $keywords, $content, $author, $published = 0)
    {
      $article = array(
        'slug' => $slug,
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'content' => $content,
        'author' => $author,
        'published' => $published,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      );

      $query = $this->db->placehold("INSERT INTO __articles SET ?%", $article);
      $this->db->query($query);

      return $this->db->insert_id();
    }

  public function update_article($id, $slug, $title, $description, $keywords, $content, $author, $published)
  {
    $article = array(
      'slug' => $slug,
      'title' => $title,
      'description' => $description,
      'keywords' => $keywords,
      'content' => $content,
      'author' => $author,
      'published' => $published,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
    );

    $query = $this->db->placehold("UPDATE __articles SET ?% WHERE id = ?", $article, (int) $id);
    $this->db->query($query);

    return $id;
  }

  public function delete_article($id)
  {
    $query = $this->db->placehold("DELETE FROM __articles WHERE id = ?", (int) $id);
    $this->db->query($query);
  }
}
