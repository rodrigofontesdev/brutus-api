<x-mail::message>
# Descubra como fazer o seu primeiro acesso!

Seja muito bem-vindo(a) à plataforma {{ config('app.name') }}! Estamos muito felizes em ter você conosco.

Nossa plataforma conta com um sistema simples e seguro para acessar sua conta, sem precisar decorar senhas! Você irá diretamente para o seu painel exclusivo na plataforma, onde poderá começar a organizar as finanças do seu negócio e ficar em dia com suas obrigações fiscais.

<x-mail::button :url="$link">
Acessar Conta
</x-mail::button>

**Importante:** Este link só pode ser usado uma vez e expira em alguns minutos. Se ele expirar, é só pedir um novo pela nossa página de login.

<br>
Atenciosamente,

**Equipe Brutus**<br>
_Simplificando a vida do MEI_
</x-mail::message>
