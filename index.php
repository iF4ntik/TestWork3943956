<?php get_header(); ?>

<main>
		<header>
			<div class="container">
				<div class="row">
					<div class="header">
			            <h1>This is a test display of products for the Abelo store</h1>
			        </div>
			        <div class="col-xl-7">
			            <div class="header-info">
				            <div class="header-info__item">
					            <img src="<?php echo get_template_directory_uri() . '/assets/img/products.svg'; ?>" alt="boxIcon">
					            <?php 
                                    $num_posts = wp_count_posts('product')->publish;
                                    echo "<a href=\"#\">{$num_posts} Products</a>";
                                ?>
				            </div>
				            <div class="header-info__item">
					            <img src="<?php echo get_template_directory_uri() . '/assets/img/at.svg'; ?>" alt="atIcon">
					            <a href="#">Photo by Eric</a>
				            </div>
			            </div>
			        </div>
			    </div>
			</div>
		</header>
        <div class="container">
            <section class="catalog">

            <?php 
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1
                );

                $loop = new WP_Query($args);
                while ($loop->have_posts()) : $loop->the_post();
                    global $product;

                    $image_url = get_post_meta(get_the_ID(), '_product_image', true);
                    $style = get_post_meta(get_the_ID(), '_product_select_option', true);
                    $price = $product->get_price_html();
            ?>

            <div class="product-item">
                <img src="<?php echo $image_url; ?>">
                <div class="product-list">
                    <h3>
                        <?php the_title(); ?>
                    </h3>
                    <p><?php echo $style; ?></p>
                    <span class="price"><?php echo $price; ?></span>
                    <a href="" class="button">Add to cart</a>
                </div>
            </div>

            <?php 
                endwhile;

                wp_reset_query(); 
            ?>
            
            </section>
        </div>

<?php get_footer(); ?>