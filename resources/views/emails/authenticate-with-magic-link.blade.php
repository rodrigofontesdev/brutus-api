<x-mail::message>
@empty(!$secretWord)
<x-mail::panel>
**{{$secretWord}}**
</x-mail::panel>
@endempty

# Seu acesso ao {{ config('app.name') }} chegou!

Recebemos uma solicitação para acessar sua conta na plataforma {{ config('app.name') }}. Para garantir a segurança dos seus dados e facilitar seu acesso, enviamos um link temporário e exclusivo.

<x-mail::button :url="$link">
Acessar Conta
</x-mail::button>

**Importante:** Este link é único e possui tempo para expirar. Ele é a chave para acessar sua área exclusiva na plataforma.

Se o link expirou você pode solicitar um novo na página de login.

<br>
Atenciosamente,

**Equipe Brutus**<br>
_Simplificando a vida do MEI_
</x-mail::message>
