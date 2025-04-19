<x-mail::message>
@empty(!$secretWord)
<x-mail::panel>
**{{$secretWord}}**
</x-mail::panel>
@endempty

# Olá {{ $firstName }},

Passando para te lembrar de uma tarefa importante para manter a organização do seu MEI em dia: a criação do relatório mensal referente a **{{ $period }}**.

Lembre-se, manter seus relatórios em dia facilita a sua gestão e evita surpresas!

<x-mail::button :url="$link">
Preencher Relatório Mensal
</x-mail::button>

<x-mail::subcopy>
Atenciosamente,

**Equipe Brutus**<br>
_Simplificando a vida do MEI_
</x-mail::subcopy>
</x-mail::message>
