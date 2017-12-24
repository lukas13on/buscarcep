# BuscarCep
Estarei tentando atualizar sempre que possível, colabore caso tenha interesse

## Sobre
Essa script valida e busca os resultados para um CEP completo, ou seja 8 digitos. As aplicações mais comuns dessa script é para validar endereço em formulários e auto preenchimento de dados, mais para frente será aprofundada outras aplicações e soluções, caso tenha sugestões envie para esse link com o titulo #sugestao

## Metodo de uso
Mude as variaveis (campo e metodo) para os valores desejado

### Retorno
Em caso de sucesso ele retornara
```json
{
  "sucesso": true,
  "numeros": [
    {
      "inicio": 1951,
      "fim": 1952
    },
    {
      "inicio": 2439,
      "fim": 2440
    }
  ],
  "rua": "Rua Guaíra",
  "bairro": "Jardim La Salle",
  "cidade": "Toledo",
  "estado": {
    "nome": "Paraná",
    "sigla": "PR"
  },
  "sede": "Curitiba",
  "estrutura": {
    "regiao": 8,
    "subregiao": 5,
    "setor": 9,
    "subsetor": 0,
    "divisao": 2,
    "distribuicao": 140
  }
}
```
Em caso de falha ele retorna
```json
{
  "sucesso": false,
  "resposta": "{Veja as respostar logo abaixo}"
}
```

### Respostas
Quando o CEP digitado não possui 8 digitos numéricos:
> O CEP é constituido por 8 numeros, verifique novamente

Quando algum erro desconhecido ocorre:
> Ocorreu um erro inesperado

Quando acontece alguma falha ao conectar ao site dos Correios
> Ocorreu uma falha ao tentar conectar aos Correios
