<?php

/* 
    Plugin Name: LP - Tempo de Leitura
    Description: Uma maneira fácil de estimar o tempo de leitura do seus posts
    Version: 1.0
    Author: LP Code
    Author URI: https://lpcode.com.br
*/

class LPTempoDeLeitura{

    function __construct(){
        add_action('admin_menu', array($this,'adminPage')); //menu
        add_action('admin_init', array($this, 'settings'));//salvando no banco de dados
        add_filter('the_content', array($this, 'ifWrap'));
    }

    function ifWrap($content){
        if(is_main_query() AND is_single() AND (get_option('tdl_letras', '1') OR get_option('tdl_palavras', '1') OR get_option('tdl_tempo', '1')) ){
            return  $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content){
       $html = '<h3> '.esc_html(get_option('tdl_titulo', 'Estatísticas do Post')).' </h3><p>';

       if(get_option('tdl_palavras', '1') OR get_option('tdl_tempo', '1')){
        $wordCount = str_word_count(strip_tags($content));
       }

       if(get_option('tdl_palavras', '1')){
        $html .= 'Esse post tem ' . $wordCount .' palavras. <br>';
       }

       if(get_option('tdl_letras', '1')){
        $html .= 'Esse post tem ' . strlen(strip_tags($content)) .' caracteres. <br>';
       }

       if(get_option('tdl_tempo', '1')){
        $html .= 'Esse post pode levar cerca de ' . round($wordCount/225) .' minuto(s) para ser lido. <br>';
       }

       $html .= '</p>';

       if(get_option('tdl_local', '0') == '0'){
            return $html . $content;
       }
       return $content . $html;
    }

    function settings(){
        add_settings_section('tdl_first_section',null,null,'lp-tempo-de-leitura');

        // Local
        add_settings_field('tdl_local', 'Inserir no:', array($this, 'localHTML'), 'lp-tempo-de-leitura', 'tdl_first_section'); //(nome,label,função do html, slug da pagina, sessão)
        register_setting('tempodeleitura', 'tdl_local', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));//(nome do grupo, nome no banco de dados, array sanitize + valor padrão)
        
        // Titulo
        add_settings_field('tdl_titulo', 'Título:', array($this, 'tituloHTML'), 'lp-tempo-de-leitura', 'tdl_first_section');
        register_setting('tempodeleitura', 'tdl_titulo', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Estatística do Post'));

        // checkbox das letras
        add_settings_field('tdl_letras', 'Contador de letras:', array($this, 'checkboxHTML'), 'lp-tempo-de-leitura', 'tdl_first_section', array('theName' => 'tdl_letras'));
        register_setting('tempodeleitura', 'tdl_letras', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')); 

        // checkbox das palavras
        add_settings_field('tdl_palavras', 'Contador de palavras:', array($this, 'checkboxHTML'), 'lp-tempo-de-leitura', 'tdl_first_section', array('theName' => 'tdl_palavras'));
        register_setting('tempodeleitura', 'tdl_palavras', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));  

        // checkbox do tempo
        add_settings_field('tdl_tempo', 'Contador de tempo:', array($this, 'checkboxHTML'), 'lp-tempo-de-leitura', 'tdl_first_section', array('theName' => 'tdl_tempo'));
        register_setting('tempodeleitura', 'tdl_tempo', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));    
    }

    function sanitizeLocation($input){
        if($input != '0' AND $input != '1'){
            add_settings_error('tdl_local', 'tdl_local_error', 'O local precisa ser no começo ou no final do post!');
            return get_option('tdl_local');
        }
        return $input;
    }

    function checkboxHTML($args){ ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName'],'1'))?> >
    <?php }

    function tituloHTML(){ ?>
 
        <input type="text" name="tdl_titulo" value="<?php echo esc_attr(get_option('tdl_titulo')) ?>">        

    <?php }

    function localHTML(){
        ?>
        <select name="tdl_local">
            <option value="0" <?php selected(get_option('tdl_local'), '0') ?>>Antes do post</option>
            <option value="1" <?php selected(get_option('tdl_local'), '1') ?>>Depois do Post</option>
        </select>
        <?php
    }

        
    function adminPage(){
        add_options_page(
            'Tempo de Leitura', //Título da página
            'Tempo de Leitura', //Título no menu
            'manage_options', //Permissão
            'lp-tempo-de-leitura', //Slug
            array($this, 'lpHTML') //Função que constrói a tela
            //pode ter a opção com o icone e depois a opção da posição
        );
    }

    function lpHTML(){
        ?>
        <div class="wrap">
            <h1>Tempo de Leitura | Opções</h1>
            <form action="options.php" method="post">
                <?php
                    settings_fields('tempodeleitura');
                    do_settings_sections('lp-tempo-de-leitura');
                    submit_button();
                ?>
            </form>
        </div>

        <?php
    }

}

$tempoDeLeitura = new LPTempoDeLeitura();

