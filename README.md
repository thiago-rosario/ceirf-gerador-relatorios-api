# CEIRF — Gerador de Relatórios API

Backend/API do **CEIRF — Gerador de Relatórios**, plataforma destinada à padronização, automatização e gestão da geração de relatórios institucionais da **Coordenação Executiva de Infraestrutura da Rede Física — CEIRF**.

O projeto foi concebido para atender diferentes coordenações e tipos de relatório. A primeira fase está concentrada no módulo **COTEC — Vistorias de Terreno**, responsável pela automatização do **Relatório de Vistoria de Terreno**.

## Sobre o projeto

Atualmente, a elaboração do Relatório de Vistoria de Terreno segue um modelo institucional com regras específicas de estrutura, conteúdo, formatação, imagens, tabelas e validações.

O **CEIRF — Gerador de Relatórios** tem como objetivo transformar esse processo em um fluxo estruturado e assistido pelo sistema, preservando o padrão institucional do documento e permitindo o preenchimento dinâmico das informações.

A API concentra as regras de negócio e os serviços necessários para criação, edição, validação, revisão, versionamento, armazenamento e geração dos relatórios.

## Escopo inicial

A primeira entrega do sistema contempla o módulo:

**COTEC — Relatórios de Vistoria de Terreno**

Entre as principais capacidades previstas estão:

- autenticação e controle de acesso;
- criação e edição de relatórios;
- preenchimento do relatório por etapas;
- salvamento automático durante o preenchimento;
- validação dos campos obrigatórios;
- gerenciamento de imagens e anexos;
- preenchimento das informações técnicas da vistoria;
- pesquisa e consulta de relatórios;
- geração do relatório final em PDF;
- versionamento de relatórios por revisão;
- revisão humana antes do encartamento no processo;
- disponibilização do documento final para visualização e download.

## Estrutura do relatório

O Relatório de Vistoria de Terreno segue a estrutura institucional definida para a CEIRF:

1. Capa
2. Sumário
3. Lista de Figuras
4. Informações Gerais
5. Objetivo
6. Localização do Terreno
7. Condições do Terreno
8. Infraestrutura Existente
9. Sugestão de Pré-implantação
10. Documentação Fotográfica
11. Conclusão
12. Anexos

O **Sumário** e a **Lista de Figuras** são gerados automaticamente pelo sistema e não constituem etapas editáveis pelo usuário.

## Fluxo de criação

Na aplicação web, a criação de um relatório é organizada em etapas:

1. Capa
2. Informações Gerais
3. Localização do Terreno
4. Infraestrutura Existente
5. Sugestão de Pré-implantação
6. Documentação Fotográfica
7. Anexos
8. Conclusão
9. Revisão Final

O usuário poderá navegar entre as etapas mesmo enquanto existirem informações pendentes.

A finalização do relatório, entretanto, deverá ser impedida enquanto existirem campos obrigatórios, perguntas obrigatórias ou opções de seleção única ainda não preenchidos.

## Revisão e versionamento

O domínio diferencia dois conceitos de revisão.

### Revisão do conteúdo

Antes de ser considerado apto para encartamento no processo, o relatório deverá passar por uma etapa de revisão humana.

Os principais perfis previstos são:

- **Usuário** — cria e preenche relatórios;
- **Reviewer** — realiza a revisão dos relatórios;
- **SuperUser** — perfil administrativo privilegiado.

As regras completas de autorização e do fluxo de aprovação serão consolidadas conforme as decisões de negócio forem formalizadas.

### Revisão documental

Depois da geração de um relatório, alterações posteriores não sobrescrevem o documento anteriormente produzido.

Uma nova versão deverá ser criada e identificada sequencialmente:

```text
REV1
REV2
REV3
...
```

Cada revisão permanece como um relatório individual, mantendo o histórico das versões anteriores.

## Identificação dos relatórios

O nome do arquivo final segue o padrão institucional:

```text
MUNICÍPIO_FORÇA_TAMANHO_TIPOLOGIA
```

Em uma revisão documental:

```text
MUNICÍPIO_FORÇA_TAMANHO_TIPOLOGIA_REVn
```

O identificador interno do relatório, o nome do arquivo e o número da revisão são conceitos relacionados, porém independentes dentro do domínio.

## Imagens e anexos

A API deverá aplicar as regras definidas para os arquivos incorporados aos relatórios, incluindo:

- limite de tamanho por arquivo;
- validação dos formatos permitidos;
- prevenção de arquivos/imagens duplicados quando aplicável;
- controle da quantidade máxima de figuras;
- numeração sequencial automática das figuras;
- gerenciamento das legendas;
- substituição e exclusão de uploads;
- obrigatoriedade de determinados documentos e imagens conforme o módulo.

A numeração das figuras deverá considerar somente os elementos efetivamente presentes no relatório final.

## Geração do PDF

O documento final deverá preservar o padrão institucional estabelecido para o Relatório de Vistoria de Terreno.

Durante sua geração, o sistema deverá:

- substituir os campos variáveis pelos dados preenchidos;
- inserir as imagens selecionadas;
- montar as tabelas a partir das respostas do usuário;
- recalcular a numeração das figuras;
- recalcular o sumário;
- recalcular a lista de figuras;
- determinar a paginação final;
- incluir somente os anexos efetivamente adicionados;
- gerar o documento final em PDF.

A formatação deverá seguir as regras definidas na especificação funcional e as diretrizes institucionais aplicáveis.

## Tecnologias

O backend é desenvolvido com:

- **PHP**
- **Laravel**
- **Composer**

O projeto é estruturado como uma API independente do cliente web.

O frontend é mantido em um repositório separado:

```text
ceirf-gerador-relatorios-web
```

A documentação funcional, de domínio, modelagem e decisões do projeto é mantida em:

```text
ceirf-gerador-relatorios-docs
```

## Desenvolvimento

Clone o repositório:

```bash
git clone https://github.com/thiago-rosario/ceirf-gerador-relatorios-api.git
cd ceirf-gerador-relatorios-api
```

Crie o arquivo de configuração local:

```bash
cp .env.example .env
```

O repositório dispõe de configuração de ambiente containerizado para desenvolvimento.

Para iniciar utilizando Docker:

```bash
docker compose up --build
```

A aplicação ficará disponível, conforme a configuração local padrão, em:

```text
http://localhost:8000
```

> As configurações de infraestrutura, persistência, armazenamento de arquivos e implantação poderão evoluir durante o desenvolvimento e devem ser consideradas definitivas somente depois de formalizadas na documentação do projeto.

## Qualidade de código

O projeto possui ferramentas para padronização, análise estática e testes automatizados.

Os comandos disponíveis pelo Composer incluem:

```bash
composer lint
composer lint:check
composer types:check
composer test
```

As alterações devem manter os testes, análise estática e padrões de código em conformidade antes da integração.

## Organização do projeto

A API deve manter separadas as responsabilidades relacionadas a:

- identidade e usuários;
- autorizações e perfis;
- coordenações;
- tipos de relatório;
- relatórios;
- revisões documentais;
- revisão humana;
- municípios e informações de domínio;
- imagens;
- anexos;
- geração de documentos.

A estrutura interna poderá evoluir conforme o domínio for implementado, priorizando separação de responsabilidades e independência das regras de negócio em relação à infraestrutura.

## Repositórios relacionados

### Documentação

```text
ceirf-gerador-relatorios-docs
```

Centraliza regras de negócio, especificações, modelagem, decisões arquiteturais e documentação técnica do projeto.

### Frontend

```text
ceirf-gerador-relatorios-web
```

Aplicação web responsável pela interface utilizada pelos usuários do CEIRF — Gerador de Relatórios.

## Status do projeto

🚧 **Em desenvolvimento**

A primeira fase está concentrada na implementação do fluxo de **Relatório de Vistoria de Terreno da COTEC**.

O produto é projetado para permitir posteriormente a inclusão de novos tipos de relatório e módulos para outras coordenações da CEIRF, sem limitar sua arquitetura ao primeiro caso de uso.

## Documentação

As regras de negócio e decisões arquiteturais devem ser consultadas prioritariamente no repositório:

```text
ceirf-gerador-relatorios-docs
```

Quando houver divergência ou uma regra ainda não definida, a decisão deve ser formalizada na documentação do projeto antes de ser tratada como comportamento definitivo da aplicação.
