Adicionar no **config/app.php**

    CristianVuolo\Uploader\Providers\UploaderServiceProvider::class

Rodar a rotina de publicação no artisan

    php artisan vendor:publish --provider=CristianVuolo\Uploader\Providers\UploaderServiceProvider

### Usage

> Fazer o chamado da classe

    use CristianVuolo\Uploader\Uploader

> Instanciar a classe, declarar o nome do arquivo (opcional) e enviar o $file com o campo o input

    $img = new Uploader('component', true);
    $img->setFilename('nome da imagem');
    $fileName = $img->upload($file);


### todo

- Testes
- Adicionar opção para enviar imagens com apenas um dos tamanhos definidos
- Fornecer Helper para chamar o arquivo de um component
- Fornecer Helper para mostrar tamanhos das imagens (em um formulário de file, por exemplo)
