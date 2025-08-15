<?php get_header(); ?>
<div class="container mx-auto px-6 py-12">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article <?php post_class('prose max-w-none'); ?>>
      <h1 class="text-4xl font-bold mb-6"><?php the_title(); ?></h1>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; else: ?>
    <p><?php esc_html_e('No content found. Create a page and set it as the homepage.', 'obti'); ?></p>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
