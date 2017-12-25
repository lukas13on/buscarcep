# BuscarCep
Estarei tentando atualizar sempre que possível, colabore caso tenha interesse

## Sobre
Essa script valida e busca os resultados para um CEP completo, ou seja 8 digitos. As aplicações mais comuns dessa script é para validar endereço em formulários e auto preenchimento de dados, mais para frente será aprofundada outras aplicações e soluções, caso tenha sugestões envie para esse [Link](https://github.com/lukas13on/buscarcep/issues) com o titulo #sugestao

## Metodo de uso
Mude as variaveis (campo e metodo) para os valores desejado

### Retorno
Em caso de sucesso ele retornara
```json
{
  "sucesso": true
}
```
Em caso de falha ele retorna
```json
{
  "sucesso": false,
  "resposta": "{Veja as respostas logo abaixo}"
}
```

### Respostas
Quando o CEP não é encontrado no sistema dos correios:
> Esse CEP não existe no sistema

Quando o CEP digitado não possui 8 digitos:
> O CEP é constituido por 8 numeros

Quando algum erro desconhecido ocorre:
> Ocorreu um erro inesperado

Quando o tempo limite de resposta é atingido (30 segundos):
> Tempo limite de resposta foi atingido

Quando ocorre algum erro no curl (veja a [lista](https://curl.haxx.se/libcurl/c/libcurl-errors.html)):
> O curl retornou código X de erro
