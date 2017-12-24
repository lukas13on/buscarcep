# BuscarCep
Estarei tentando atualizar sempre que possível, colabore caso tenha interesse

## Sobre
Essa script valida e busca os resultados para cep completo, ou seja 8 digitos. As aplicações mais comuns dessa script é para validar endereço em formulários e auto preenchimento de dados, mais para frente será aprofundada outras aplicações e soluções, caso tenha sugestões envie para esse link com o titulo #sugestao

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
  "sucesso": false
}
```
