<?php
/**
 * Template de Comentarios Personalizado en Español
 */

if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ( $comment_count === 1 ) {
                echo '1 Comentario';
            } else {
                printf( '%s Comentarios', number_format_i18n( $comment_count ) );
            }
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 50,
            ) );
            ?>
        </ol>

        <?php
        the_comments_navigation( array(
            'prev_text' => '← Comentarios anteriores',
            'next_text' => 'Comentarios nuevos →',
        ) );
        ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="no-comments">Los comentarios están cerrados.</p>
    <?php endif; ?>

    <?php
    comment_form( array(
        'title_reply'          => 'Deja un Comentario',
        'title_reply_to'       => 'Responder a %s',
        'cancel_reply_link'    => 'Cancelar respuesta',
        'label_submit'         => 'Publicar Comentario',
        'comment_field'        => '<p class="comment-form-comment"><label for="comment">Comentario <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
        'fields'               => array(
            'author'  => '<p class="comment-form-author"><label for="author">Nombre <span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr( isset($_POST['author']) ? $_POST['author'] : '' ) . '" size="30" required /></p>',
            'email'   => '<p class="comment-form-email"><label for="email">Correo Electrónico <span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr( isset($_POST['email']) ? $_POST['email'] : '' ) . '" size="30" required /></p>',
            'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /><label for="wp-comment-cookies-consent">Guardar mi nombre, correo electrónico y sitio web en este navegador para la próxima vez que comente.</label></p>',
        ),
        'comment_notes_before' => '<p class="comment-notes">Tu correo electrónico no será publicado. Los campos obligatorios están marcados con <span class="required">*</span></p>',
        'comment_notes_after'  => '',
    ) );
    ?>

</div>
