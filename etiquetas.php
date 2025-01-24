<?php
	
	
	
	
function etiquetas_nicolini($usuario, $modeloetq, $id)	
{
	// FAZ UM SELECT PARA VER QUAL TIPO DE ETQ E QUAL USUÁRIO PARA IMPRESSÃO;
	sc_select(rs, "SELECT ModeloEtq, qusuario, loja from tmp_impetq where qusuario = '".$usuario."' AND ModeloEtq = '".$modeloetq."' GROUP BY Codigoint ORDER BY ModeloEtq");	
		
	
	$modeloetq = $rs->fields[0];
	$usuario = $rs->fields[1];		
	$vloja =$rs->fields[2];
	

    if ($modeloetq == "ETQCV" || $modeloetq==="0000") {
        $sql_impETQCV = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ETQCV' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";
        

        $nm_select = $sql_impETQCV;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_impETQCV = $this->Db->Execute($nm_select)) {
            while (!$this->array_impETQCV->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_impETQCV->fields['loja'];
                $modelo_etiqueta = $this->array_impETQCV->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_impETQCV->fields['codigo_interno'];
                $descricao_produto = $this->array_impETQCV->fields['descricao_produto'];
                $codigo_ean = $this->array_impETQCV->fields['codigo_ean'];
                $venda_loja = $this->array_impETQCV->fields['venda_loja'];
                $unidade_medida = $this->array_impETQCV->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_impETQCV->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_impETQCV->fields['valor_gelado'];
                $limite = $this->array_impETQCV->fields['limite'];
                $preco_clube = $this->array_impETQCV->fields['preco_clube'];
                $validade = $this->array_impETQCV->fields['validade'];
                $unidade_conversao = $this->array_impETQCV->fields['unidade_conversao'];
                $codigo_unidade = $this->array_impETQCV->fields['codigo_unidade'];
                $valor_conv = $this->array_impETQCV->fields['valor_conv'];
                $precoetq = $this->array_impETQCV->fields['precoetq'];
                $dataAtual = $this->array_impETQCV->fields['data_atual'];

                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }

                


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                if ($preco_promocional_conversao > 0) {
                    $msg .= "221100001200295"."#>P<#"."Imp: ".$descricao_produto + $dataAtual + " ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "221100001200295"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                }

                $msg .= "131200001000010".$descricao_produto."\r"."\n";
                $msg .= "131100000770144" + "  *** CLUBE ***";
                $msg .= "132300000280040".$precoetq."\r"."\n";
                $msg .= "132300000300175".$preco_clube."\r"."\n";
                $msg .= "122100000330005" + "R$";
                $msg .= "122100000330150" + "R$";
                $msg .= "121100000120012".$codigo_ean."\r"."\n";
                $msg .= "121100100210150" + " DESCONTO EM ATE " .$limite + $codigo_unidade."\r"."\n";
                $msg .="121100100110170" + " ATE ".$validade."\r"."\n";
                $msg .="1X1100000750139L140020"; 

                $msg .="42110000015286" + $valor_conv . "" . $unidade_conversao . ":R$" . $PrecoEquiv;
                $msg .="42110000023135" + $valor_conv . "" . $unidade_conversao . ":R$" . $PrecoEquiv2;

                $msg.="E";

                $this->array_impETQCV->MoveNext();
            }
            $this->array_impETQCV->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_impETQCV = false;
            $this->array_impETQCV_erro = $this->Db->ErrorMsg();
        }
    }

    if ($modeloetq == "ETQC1" || $modeloetq==="0000") {
        $IMpETQC1 = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ETQC1' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";

        $nm_select = $IMpETQC1;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_IMpETQC1 = $this->Db->Execute($nm_select)) {
            while (!$this->array_IMpETQC1->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_IMpETQC1->fields['loja'];
                $modelo_etiqueta = $this->array_IMpETQC1->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_IMpETQC1->fields['codigo_interno'];
                $descricao_produto = $this->array_IMpETQC1->fields['descricao_produto'];
                $codigo_ean = $this->array_IMpETQC1->fields['codigo_ean'];
                $venda_loja = $this->array_IMpETQC1->fields['venda_loja'];
                $unidade_medida = $this->array_IMpETQC1->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_IMpETQC1->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_IMpETQC1->fields['valor_gelado'];
                $limite = $this->array_IMpETQC1->fields['limite'];
                $preco_clube = $this->array_IMpETQC1->fields['preco_clube'];
                $validade = $this->array_IMpETQC1->fields['validade'];
                $unidade_conversao = $this->array_IMpETQC1->fields['unidade_conversao'];
                $valor_conv = $this->array_IMpETQC1->fields['valor_conv'];
                $precoetq = $this->array_IMpETQC1->fields['precoetq'];

                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida  > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                if ($preco_promocional_conversao > 0) {
                    $msg .= "221100001200295"."#>P<#"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "221100001200295"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                }

                $msg .= "131200001000010".$descricao_produto."\r"."\n";
                $msg .= "131100000770144" + "  *** CLUBE ***";

                $msg .= "131100000770005" + " A CADA 100g";
                $msg .= "132300000280040" . ($precoetq / 10)."\r"."\n";
                $msg .= "132300000300175".($preco_clube/10)."\r"."\n";
                $msg .= "122100000330005" + "R$";
                $msg .= "122100000330150" + "R$";
                $msg .= "121100000120012".$codigo_ean."\r"."\n";
                $msg .= "121100100210150" + " DESCONTO EM ATE " .$limite + $codigo_unidade."\r"."\n";
                $msg .="121100100110170" + " ATE ".$validade."\r"."\n";
                $msg .="1X1100000750139L140020"; //caixa preta

                $msg .="42110000015286" + $valor_conv . "" . $unidade_conversao . ":R$" . $PrecoEquiv;
                $msg .="42110000023135" + $valor_conv . "" . $unidade_conversao . ":R$" . $PrecoEquiv2;

                $msg.="E";

                $this->array_IMpETQC1->MoveNext();
            }
            $this->array_IMpETQC1->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_IMpETQC1 = false;
            $this->array_IMpETQC1_erro = $this->Db->ErrorMsg();
        }
    }

    if ($modeloetq == "ALGG" || $modeloetq==="0000") {
        $ALGG = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ALGG' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";

        $nm_select = $ALGG;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_ALGG = $this->Db->Execute($nm_select)) {
            while (!$this->array_ALGG->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_ALGG->fields['loja'];
                $modelo_etiqueta = $this->array_ALGG->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_ALGG->fields['codigo_interno'];
                $descricao_produto = $this->array_ALGG->fields['descricao_produto'];
                $codigo_ean = $this->array_ALGG->fields['codigo_ean'];
                $venda_loja = $this->array_ALGG->fields['venda_loja'];
                $unidade_medida = $this->array_ALGG->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_ALGG->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_ALGG->fields['valor_gelado'];
                $limite = $this->array_ALGG->fields['limite'];
                $preco_clube = $this->array_ALGG->fields['preco_clube'];
                $validade = $this->array_ALGG->fields['validade'];
                $unidade_conversao = $this->array_ALGG->fields['unidade_conversao'];
                $codigo_unidade = $this->array_ALGG->fields['codigo_unidade'];
                $valor_conv = $this->array_ALGG->fields['valor_conv'];
                $precoetq = $this->array_ALGG->fields['precoetq'];
                $dataAtual = $this->array_ALGG->fields['data_atual'];

                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida  > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                $msg .= "131200000750020" + $descricao_produto; //etiqueta texto?
                $msg .= "131100000250150R$";
                $msg .= "131100000300260" + $codigo_unidade;
                $msg .= "132300000250170" + $precoetq;

                if ($preco_promocional_conversao > 0) {
                    $msg .= "121100000200010"."#>P<#"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "121100000200010"."Imp: ".$descricao_produto + $dataAtual + " ".$EndPeng."\r"."\n";
                }

                $msg .="121000000130140" + $valor_conv . "" . $unidade_conversao . ":R$" . $PrecoEquiv2;

                $msg .= "1G1204000350010".$codigo_ean."\r"."\n";

                $msg.="E";

                $this->array_ALGG->MoveNext();
            }
            $this->array_ALGG->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_ALGG = false;
            $this->array_ALGG_erro = $this->Db->ErrorMsg();
        }
    }

    if ($modeloetq == "ALG1" || $modeloetq==="0000") {
        $ALG1 = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq,
                cad_mercloja.lotefabricante,
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ALG1' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";

        $nm_select = $ALG1;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_ALG1 = $this->Db->Execute($nm_select)) {
            while (!$this->array_ALG1->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_ALG1->fields['loja'];
                $modelo_etiqueta = $this->array_ALG1->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_ALG1->fields['codigo_interno'];
                $descricao_produto = $this->array_ALG1->fields['descricao_produto'];
                $codigo_ean = $this->array_ALG1->fields['codigo_ean'];
                $venda_loja = $this->array_ALG1->fields['venda_loja'];
                $unidade_medida = $this->array_ALG1->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_ALG1->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_ALG1->fields['valor_gelado'];
                $limite = $this->array_ALG1->fields['limite'];
                $preco_clube = $this->array_ALG1->fields['preco_clube'];
                $validade = $this->array_ALG1->fields['validade'];
                $unidade_conversao = $this->array_ALG1->fields['unidade_conversao'];
                $valor_conv = $this->array_ALG1->fields['valor_conv'];
                $precoetq = $this->array_ALG1->fields['precoetq'];
                $lotefabricante = $this->array_algg->fields['lotefabricante'];


                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida  > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                $msg .= "131200001000010" + $descricao_produto; //etiqueta texto?
                $msg .= "131100000250150R$";

                $msg .= "131100000770005" + " A CADA 100g";
                $msg .= "131100000750150" . ($precoetq / 10)."\r"."\n";
        

                if ($preco_promocional_conversao > 0) {
                    $msg .= "221100001200295"."#>P<#"."Imp: ".$descricao_produto." ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "221100001200295"."Imp: ".$descricao_produto." ".$EndPeng."\r"."\n";
                }

                $msg .= "131100000600030" + $lotefabricante;

                $msg .= "131100000370030" + "Validade:";

                if ((int)$validade === 0) {
                    $sval = 1;
                } else {
                    $sval = $validade;
                }
                
                $msg .= "131100000170030" + ($dataAtual + $sval -1);
                $msg .= "131100000230250" + $codigo_unidade;

                $msg.="E";

                $this->array_ALG1->MoveNext();
            }
            $this->array_ALG1->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_ALG1 = false;
            $this->array_ALG1_erro = $this->Db->ErrorMsg();
        }
    }

    if ($modeloetq == "ALGGA" || $modeloetq==="0000") {
        #######################################################################################################################
        ################################################## ALGGA ##############################################################
        ############################################## ETIQUETA 1.6 GÔNDOLA B. ATENDIM ########################################
        #######################################################################################################################

        $sql_algga = "
            SELECT 
                tmp_impetq.Loja, tmp_impetq.ModeloEtq, tmp_impetq.CODIGOINT, tmp_impetq.desc_imp, 
                cad_mercador.CODIGOEAN, cad_mercloja.vendaloja AS vendaloja, cad_mercador.unidademedida, 
                cad_mercador.unidadeconv, cad_mercador.CODIGOUNI, cad_mercloja.PrecoProm, cad_mercador.nomeres,
                cad_mercador.vendaatacado, tmp_impetq.Qtd, tmp_impetq.id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS dataatual, 
                ROUND(((1 / cad_mercador.unidademedida) * cad_mercloja.VENDALOJA), 2) AS precoconv,
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero,
                ROUND(((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm), 2) AS precopromconv,
                tmp_impetq.gelado, cad_mercador.vlresp1,
                cad_mercloja.lotefabricante,
                tmp_impetq.precoetq,
                tmp_impetq.validade,
                tmp_impetq.descorigem
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ALGGA' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '$usuario' 
                AND tmp_impetq.id = '$id' 
            ORDER BY 
                tmp_impetq.descricao";

        $nm_select = $sql_algga;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';
        if ($this->array_algga = $this->Db->Execute($nm_select)) {
            // Process rows
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_algga = false;
            $this->array_algga_erro = $this->Db->ErrorMsg();
        }

        while (!$this->array_algga->EOF) {
            // Nomeando as variáveis para maior legibilidade
            $loja = $this->array_algga->fields[0];
            $modeloEtq = $this->array_algga->fields[1];
            $codigoInt = $this->array_algga->fields[2];
            $descricaoProduto = $this->array_algga->fields[3];
            $codigoEAN = $this->array_algga->fields[4];
            $vendaLoja = $this->array_algga->fields[5];
            $unidadeMedida = $this->array_algga->fields[6];
            $unidadeConv = $this->array_algga->fields[7];
            $codigo_unidade = $this->array_algga->fields[8];
            $precoProm = $this->array_algga->fields[9];
            $nomeRes = $this->array_algga->fields[10];
            $vendaAtacado = $this->array_algga->fields[11];
            $quantidade = $this->array_algga->fields[12];
            $id = $this->array_algga->fields[13];
            $dataAtual = $this->array_algga->fields[14];
            $precoConv = $this->array_algga->fields[15];
            $numero = $this->array_algga->fields[16];
            $precoPromConv = $this->array_algga->fields[17];
            $gelado = $this->array_algga->fields[18];
            $vlResp1 = $this->array_algga->fields[19];
            $lotefabricante = $this->array_algga->fields[20];
            $precoetq = $this->array_algga->fields[21];
            $validade = $this->array_algga->fields[22];
            $descorigem = $this->array_algga->fields[23];
            $sval = 1;

            sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

            $vGelado = 0;
            if ($gelado == -1) {
                $vGelado = round($vlResp1, 2);
                $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado $vlResp1");
            }

            if (($precoProm == 0) || empty($precoProm)) {
                $vPrcUni = round($vendaLoja + $vGelado, 2);
            } else {
                $vPrcUni = round($precoProm + $vGelado, 2);
            }

            $msg = "<stx>L\r\n";
            $msg .= "D11\r\n";
            $msg .= "H13\r\n";
            $msg .= "PC\r\n";
            $msg .= "Q0001\r\n";
            $msg .= "131200001000010" + $descricaoProduto;
            $msg .= "131100000230150R$";

            if ($preco_promocional_conversao > 0) {
                    $msg .= "221100001200295"."#>P<#"."Imp: ".$descricao_produto." ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "221100001200295"."Imp: ".$descricao_produto." ".$EndPeng."\r"."\n";
                };

            $msg .= "131100000600030" + $lotefabricante;
            $msg .= "132300000180170" + $precoetq;
            if ((int)$validade === 0) {
                $sval = 1;
            } else {
                $sval = $validade;
            };
            $msg .= "131100000370030" + "Validade:";

            
            
            $msg .= "131100000170030" + ($dataAtual + $sval -1);
            $msg .= "131100000230250" + $codigo_unidade;
            $msg .= "121100000900010" . substr($descorigem, 0, 45) . "\r\n";
            $msg .= "121100000800010" . substr($descorigem, 46, 45) . "\r\n";
            $msg .= "E";
            
            $this->array_algga->MoveNext();
        }
        $this->array_algga->Close();
    }

    if ($modeloetq == "ALGGC" || $modeloetq==="0000") {
        $ALGGC = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ALGGC' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";

        $nm_select = $ALGGC;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_ALGGC = $this->Db->Execute($nm_select)) {
            while (!$this->array_ALGGC->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_ALGGC->fields['loja'];
                $modelo_etiqueta = $this->array_ALGGC->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_ALGGC->fields['codigo_interno'];
                $descricao_produto = $this->array_ALGGC->fields['descricao_produto'];
                $codigo_ean = $this->array_ALGGC->fields['codigo_ean'];
                $venda_loja = $this->array_ALGGC->fields['venda_loja'];
                $unidade_medida = $this->array_ALGGC->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_ALGGC->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_ALGGC->fields['valor_gelado'];
                $limite = $this->array_ALGGC->fields['limite'];
                $preco_clube = $this->array_ALGGC->fields['preco_clube'];
                $validade = $this->array_ALGGC->fields['validade'];
                $unidade_conversao = $this->array_ALGGC->fields['unidade_conversao'];
                $valor_conv = $this->array_ALGGC->fields['valor_conv'];
                $precoetq = $this->array_ALGGC->fields['precoetq'];

                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida  > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                $msg .= "131200000900040" + $descricao_produto; //etiqueta texto?
        

                if ($preco_promocional_conversao > 0) {
                    $msg .= "221100001200295"."#>P<#"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                } else {
                    $msg .= "221100001200295"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                }

                $msg .= "131100000600040" + $lotefabricante;

                if ((int)$validade === 0) {
                    $sval = 1;
                } else {
                    $sval = $validade;
                }

                $msg .= "131100000370040" + "Validade:" +  ($dataAtual + $sval -1);

                $msg .= "121100000130040" + $descorigem;

                $msg.="E";

                $this->array_ALGGC->MoveNext();
            }
            $this->array_ALGGC->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_ALGGC = false;
            $this->array_ALGGC = $this->Db->ErrorMsg();
        }



        
        return($msg);	
        
    } 

    if ($modeloetq == "ALGGH" || $modeloetq==="0000") {
        $ALGGH = "
            SELECT 
                tmp_impetq.Loja AS loja, 
                tmp_impetq.ModeloEtq AS modelo_etiqueta, 
                tmp_impetq.CODIGOINT AS codigo_interno, 
                tmp_impetq.desc_imp AS descricao_produto, 
                cad_mercador.CODIGOEAN AS codigo_ean, 
                cad_mercloja.vendaloja AS venda_loja, 
                cad_mercador.unidademedida AS unidade_medida, 
                cad_mercador.unidadeconv AS unidade_conversao, 
                cad_mercador.CODIGOUNI AS codigo_unidade, 
                cad_mercador.valorconv as valor_conv,
                cad_mercloja.PrecoProm AS preco_promocional, 
                cad_mercador.nomeres AS nome_resumido, 
                cad_mercador.vendaatacado AS venda_atacado, 
                tmp_impetq.Qtd AS quantidade, 
                tmp_impetq.id AS id, 
                DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS data_atual, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.vendaloja, 2) AS preco_conversao, 
                IF(1, cad_mercador.unidademedida > 0, NULL) AS numero, 
                ROUND((1 / cad_mercador.unidademedida) * cad_mercloja.PrecoProm, 2) AS preco_promocional_conversao, 
                tmp_impetq.gelado AS valor_gelado, 
                cad_mercador.vlresp1 AS valor_responsavel, 
                tmp_impetq.prcclube AS preco_clube, 
                tmp_impetq.limite AS limite,
                tmp_impetq.validade as validade,
                tmp_impetq.precoetq as precoetq
            FROM 
                tmp_impetq 
            INNER JOIN 
                cad_mercador ON tmp_impetq.codigoint = cad_mercador.codigoint
            INNER JOIN 
                cad_mercloja ON cad_mercloja.codigoint = tmp_impetq.codigoint 
                            AND cad_mercloja.Loja = tmp_impetq.Loja
            WHERE 
                tmp_impetq.ModeloEtq = 'ALGGH' 
                AND cad_mercloja.bloqetq = 0 
                AND cad_mercloja.ativa = '-1' 
                AND tmp_impetq.qusuario = '".$usuario."' 
                AND tmp_impetq.id = '".$id."' 
            ORDER BY 
                tmp_impetq.descricao
        ";

        $nm_select = $ALGGH;
        $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select;
        $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';

        if ($this->array_ALGGH = $this->Db->Execute($nm_select)) {
            while (!$this->array_ALGGH->EOF) {
                // Atribuindo variáveis nomeadas
                $loja = $this->array_ALGGH->fields['loja'];
                $modelo_etiqueta = $this->array_ALGGH->fields['modelo_etiqueta'];
                $codigo_interno = $this->array_ALGGH->fields['codigo_interno'];
                $descricao_produto = $this->array_ALGGH->fields['descricao_produto'];
                $codigo_ean = $this->array_ALGGH->fields['codigo_ean'];
                $venda_loja = $this->array_ALGGH->fields['venda_loja'];
                $unidade_medida = $this->array_ALGGH->fields['unidade_medida'];
                $preco_promocional_conversao = $this->array_ALGGH->fields['preco_promocional_conversao'];
                $valor_gelado = $this->array_ALGGH->fields['valor_gelado'];
                $limite = $this->array_ALGGH->fields['limite'];
                $preco_clube = $this->array_ALGGH->fields['preco_clube'];
                $validade = $this->array_ALGGH->fields['validade'];
                $unidade_conversao = $this->array_ALGGH->fields['unidade_conversao'];
                $valor_conv = $this->array_ALGGH->fields['valor_conv'];
                $precoetq = $this->array_ALGGH->fields['precoetq'];

                sc_select(gond,"Select gondola_rua,gondola_modulo from cad_mercloja where cad_mercloja.codigoint='".$codigo_interno."'");
		
	          $Gond_rua = $gond->fields[0];  
	          $Gond_mod = $gond->fields[1];
	
        	  $EndPeng = "R: " .$Gond_rua. "M: " .$Gond_mod;

                if($unidade_medida  > 0 & $valor_conv > 0){
                $PrecoEquiv = ($valor_conv * $preco_clube) / $unidade_medida;
                $PrecoEquiv2 = ($valor_conv * $precoetq) / $unidade_medida;
                }


                $vgelado = 0;

                if ($preco_promocional_conversao == -1) {
                    $vgelado = round($valor_gelado, 2);
                    $this->NM_gera_log_insert("User", "processo", "etiqueta vlr gelado ".$valor_gelado);
                }

                $msg = "<stx>L"."\r"."\n";
                $msg .= "D11"."\r"."\n";
                $msg .= "H13"."\r"."\n";
                $msg .= "PC"."\r"."\n";
                $msg .= "Q0001"."\r"."\n";

                $msg .= "131200000900040" + $descricao_produto; //etiqueta texto?

                $msg .= "131100000200150R$";
        

               
                $msg .= "121100000000320"."Imp: ".$descricao_produto. $dataAtual + " ".$EndPeng."\r"."\n";
                

                $msg .= "131100000500020" + $lotefabricante;

                $msg .= "142300000170170" + $precoetq;

                $msg .= "131100000200280" + $codigo_unidade;

                $msg .= "121100000130040" + $descorigem;

                $msg.="E";

                $this->array_ALGGH->MoveNext();
            }
            $this->array_ALGGH->Close();
        } elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1) {
            $this->array_ALGGH = false;
            $this->array_ALGGH = $this->Db->ErrorMsg();
        }



        
        return($msg);	
        
    } 

    if($modeloetq == "ETQAT" || $modeloetq==="0000")

	{

		$sql_atac = "SELECT tmp_impetq.Loja, tmp_impetq.ModeloEtq, tmp_impetq.CODIGOINT, tmp_impetq.desc_imp, 

		cad_mercador.CODIGOEAN, cad_mercloja.vendaloja as vendaloja, cad_mercador.unidademedida, 

		cad_mercador.unidadeconv, cad_mercador.CODIGOUNI, cad_mercloja.PrecoProm, cad_mercador.nomeres,

		cad_mercador.vendaatacado, tmp_impetq.Qtd, tmp_impetq.id, date_format(curdate(), '%d/%m/%Y') as dataatual, 

		round((( 1 / cad_mercador.unidademedida)*cad_mercloja.VENDALOJA), 2) as precoconv,

		if(1, cad_mercador.unidademedida > 0, null) as numero,

		round((( 1 / cad_mercador.unidademedida)*cad_mercloja.PrecoProm), 2) as precopromconv 

		FROM tmp_impetq INNER JOIN cad_mercador ON (tmp_impetq.codigoint = cad_mercador.codigoint)

		INNER JOIN cad_mercloja ON (cad_mercloja.codigoint = tmp_impetq.codigoint) and (cad_mercloja.Loja = tmp_impetq.Loja)

		WHERE tmp_impetq.ModeloEtq = 'ETQAT' and cad_mercloja.bloqetq = 0 and cad_mercloja.ativa = '-1' 

		and tmp_impetq.qusuario = '".$usuario."' and tmp_impetq.id = '".$id."' ORDER BY tmp_impetq.descricao";



		 
      $nm_select = $sql_atac; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->array_atac = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->array_atac = false;
          $this->array_atac_erro = $this->Db->ErrorMsg();
      } 
;

		while(!$this->array_atac->EOF)

		{		
			$vprcat = 0;
			$vqtdat = 0;
			$vunat = " ";
			$vprcpack = 0;
			$vqtdpack = 0;
			$vunpack = " ";
			$vprcuni = 0;

			if(($this->array_atac->fields[9] == 0) || (empty($this->array_atac->fields[9])))
			{

				$vprcuni = round($this->array_atac->fields[5], 2); 
			}
			else
			{
			$vprcuni = round($this->array_atac->fields[9], 2); 
			}

			if(($this->array_atac->fields[9] == 0) || ($this->array_atac->fields[9] == null))

			{

				 
      $nm_select = "SELECT unidedi, CODIGOEANEDI, Fatoruni, FatConversao, lojas, (FatConversao - 1) as vqtdatEtiq FROM cad_eanedi WHERE unidedi <> 'PV' AND Fatoruni > 0 AND bloqpdv = 0 AND codigoint = '".$this->array_atac->fields[2]."' AND (lojas like '%".$this->array_atac->fields[0]."%' OR lojas = 'Todas')"; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->vrv_embatac = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->vrv_embatac = false;
          $this->vrv_embatac_erro = $this->Db->ErrorMsg();
      } 
;
				if($this->vrv_embatac  !== false)
				{

					$vprcat = round(($this->array_atac->fields[5] * $this->vrv_embatac->fields[2]), 2); 
					$vqtdat = $this->vrv_embatac->fields[3]; 
					$vunat = $this->vrv_embatac->fields[0]; 
					$veanat = $this->vrv_embatac->fields[1]; 
					$vtat = ($vprcat * $vqtdat); 
				} 
			
			} 

			if(($this->array_atac->fields[9] == 0) || ($this->array_atac->fields[9] == null))

			{

				 
      $nm_select = "SELECT unidedi, CODIGOEANEDI, Fatoruni, FatConversao, lojas FROM cad_eanedi WHERE

	unidedi = 'PV' AND Fatoruni > 0 AND bloqpdv = 0 AND codigoint = '".$this->array_atac->fields[2]."' AND (lojas like '%".$this->array_atac->fields[0]."%' OR lojas = 'Todas')"; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->vrv_packvirtual = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->vrv_packvirtual = false;
          $this->vrv_packvirtual_erro = $this->Db->ErrorMsg();
      } 
;
				if($this->vrv_packvirtual  !== false)
				{
					if($this->vrv_packvirtual->fields[2] <> 1) 

					{

						$vprcpack = round(($this->array_atac->fields[5] * $this->vrv_packvirtual->fields[2]), 2); 
						$vqtdpack = $this->vrv_packvirtual->fields[3]; 
						$vunpack = $this->array_atac->fields[8]; 
					} 
					

					if($this->array_atac->fields[4] == " ")

					{

						$vprcat = round(($this->array_atac->fields[5] * $this->vrv_packvirtual->fields[2]), 2); 
						$vqtdat = $this->vrv_packvirtual->fields[3]; 
						$vunat = $this->vrv_packvirtual->fields[0]; 
					} 
				} 
			
			} 
		

			if(($this->array_atac->fields[9] > 0) || ($vprcpack == $vprcuni && $vprcat == $vprcuni) || ($vprcat == 0 && $vprcpack == 0) || ($vprcpack == 0 && $vprcat == $vprcuni))

			{	

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500170Preco"."\r"."\n";

				$msg  .="231100003500150Unitario"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="262200002300130".number_format($vprcuni, 2, ',', '.')."\r"."\n"; 
				$msg  .="E"."\r"."\n";					

			}

			elseif($vprcpack == 0)

			{

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500230Preco UN."."\r"."\n"; 

				$msg  .="231100003500210Atacado."."\r"."\n";

				$msg  .="231100003500190Emb. Fechada"."\r"."\n";

				$msg  .="231100003500140Preco"."\r"."\n";

				$msg  .="231100003500120Varejo"."\r"."\n";

				$msg  .="231100003500100Und. Avulsa"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="262200002300185".number_format($vprcat, 2, ',', '.')."\r"."\n";  
				$msg  .="262200002300085".number_format($vprcuni, 2, ',', '.')."\r"."\n";  
				$msg  .="212300003500040" . $veanat . " Emb com " . $vqtdat .  " R$ " . number_format($vtat, 2, ',', '.')."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";					

			}

			elseif(($vprcat == 0) || ($vprcat == $vprcuni))

			{

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="241200003700265Preco Qtd"."\r"."\n";

				$msg  .="231100003500210A partir de"."\r"."\n";

				$msg  .="2311000035001908" . $vqtdpack . " " . $vunpack ."\r"."\n"; 
				$msg  .="231100003500140Preco"."\r"."\n";

				$msg  .="231100003500120varejo"."\r"."\n";

				$msg  .="231100003500100Und. Avulsa"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="262200002300185".number_format($vprcpack, 2, ',', '.')."\r"."\n";  
				$msg  .="262200002300085".number_format($vprcuni, 2, ',', '.')."\r"."\n";    
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";		

			}

			else
			{	

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265 " .$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500240Preco"."\r"."\n";

				$msg  .="231100003500220Atacado"."\r"."\n";

				$msg  .="231100003500200Emb. Fechada"."\r"."\n";

				$msg  .="231100003500170Preco Qtd"."\r"."\n";

				$msg  .="231100003500150A partir de"."\r"."\n";

				$msg  .="232100003500130" . $vqtdpack . " " . $vunpack ."\r"."\n"; 
				$msg  .="231100003500100Preco"."\r"."\n";

				$msg  .="231100003500080Varejo"."\r"."\n";

				$msg  .="231100003500060Und. Avulsa"."\r"."\n";

				$msg  .="255200002550195".number_format($vprcat, 2, ',', '.')."\r"."\n";  
				$msg  .="255200002550130".number_format($vprcpack, 2, ',', '.')."\r"."\n";  
				$msg  .="255200002550065".number_format($vprcuni, 2, ',', '.')."\r"."\n";    
				$msg  .="2F5302001700265 " .$this->array_atac->fields[4]. "\r"."\n"; 
				$msg  .="212300003500010 " . $veanat . " Emb com " . $vqtdat . " R$ " . number_format($vtat, 2, ',', '.') ."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";	
			}  
			$this->array_atac->MoveNext();		
		} 
		$this->array_atac->Close();

	}

    if($modeloetq == "EATCX" || $modeloetq==="0000")

	{

		$sql_atac = "SELECT tmp_impetq.Loja, tmp_impetq.ModeloEtq, tmp_impetq.CODIGOINT, tmp_impetq.desc_imp, 

		cad_mercador.CODIGOEAN, cad_mercloja.vendaloja as vendaloja, cad_mercador.unidademedida, 

		cad_mercador.unidadeconv, cad_mercador.CODIGOUNI, cad_mercloja.PrecoProm, cad_mercador.nomeres,

		cad_mercador.vendaatacado, tmp_impetq.Qtd, tmp_impetq.id, date_format(curdate(), '%d/%m/%Y') as dataatual, 

		round((( 1 / cad_mercador.unidademedida)*cad_mercloja.VENDALOJA), 2) as precoconv,

		if(1, cad_mercador.unidademedida > 0, null) as numero,

		round((( 1 / cad_mercador.unidademedida)*cad_mercloja.PrecoProm), 2) as precopromconv 

		FROM tmp_impetq INNER JOIN cad_mercador ON (tmp_impetq.codigoint = cad_mercador.codigoint)

		INNER JOIN cad_mercloja ON (cad_mercloja.codigoint = tmp_impetq.codigoint) and (cad_mercloja.Loja = tmp_impetq.Loja)

		WHERE tmp_impetq.ModeloEtq = 'EATCX' and cad_mercloja.bloqetq = 0 and cad_mercloja.ativa = '-1' 

		and tmp_impetq.qusuario = '".$usuario."' and tmp_impetq.id = '".$id."' ORDER BY tmp_impetq.descricao";



		 
      $nm_select = $sql_atac; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->array_atac = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->array_atac = false;
          $this->array_atac_erro = $this->Db->ErrorMsg();
      } 
;

		while(!$this->array_atac->EOF)

		{		
			$vprcat = 0;
			$vqtdat = 0;
			$vunat = " ";
			$vprcpack = 0;
			$vqtdpack = 0;
			$vunpack = " ";
			$vprcuni = 0;

			if(($this->array_atac->fields[9] == 0) || (empty($this->array_atac->fields[9])))
			{

				$vprcuni = round($this->array_atac->fields[5], 2); 
			}
			else
			{
			$vprcuni = round($this->array_atac->fields[9], 2); 
			}

			if(($this->array_atac->fields[9] == 0) || ($this->array_atac->fields[9] == null))

			{

				 
      $nm_select = "SELECT unidedi, CODIGOEANEDI, Fatoruni, FatConversao, lojas, (FatConversao - 1) as vqtdatEtiq FROM cad_eanedi WHERE unidedi <> 'PV' AND Fatoruni > 0 AND bloqpdv = 0 AND codigoint = '".$this->array_atac->fields[2]."' AND (lojas like '%".$this->array_atac->fields[0]."%' OR lojas = 'Todas')"; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->vrv_embatac = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->vrv_embatac = false;
          $this->vrv_embatac_erro = $this->Db->ErrorMsg();
      } 
;
				if($this->vrv_embatac  !== false)
				{

					$vprcat = round(($this->array_atac->fields[5] * $this->vrv_embatac->fields[2]), 2); 
					$vqtdat = $this->vrv_embatac->fields[3]; 
					$vunat = $this->vrv_embatac->fields[0]; 
					$veanat = $this->vrv_embatac->fields[1]; 
					$vtat = ($vprcat * $vqtdat); 
				} 
			
			} 

			if(($this->array_atac->fields[9] == 0) || ($this->array_atac->fields[9] == null))

			{

				 
      $nm_select = "SELECT unidedi, CODIGOEANEDI, Fatoruni, FatConversao, lojas FROM cad_eanedi WHERE

	unidedi = 'PV' AND Fatoruni > 0 AND bloqpdv = 0 AND codigoint = '".$this->array_atac->fields[2]."' AND (lojas like '%".$this->array_atac->fields[0]."%' OR lojas = 'Todas')"; 
      $_SESSION['scriptcase']['sc_sql_ult_comando'] = $nm_select; 
      $_SESSION['scriptcase']['sc_sql_ult_conexao'] = ''; 
      if ($this->vrv_packvirtual = $this->Db->Execute($nm_select)) 
      { }
      elseif (isset($GLOBALS["NM_ERRO_IBASE"]) && $GLOBALS["NM_ERRO_IBASE"] != 1)  
      { 
          $this->vrv_packvirtual = false;
          $this->vrv_packvirtual_erro = $this->Db->ErrorMsg();
      } 
;
				if($this->vrv_packvirtual  !== false)
				{
					if($this->vrv_packvirtual->fields[2] <> 1) 

					{

						$vprcpack = round(($this->array_atac->fields[5] * $this->vrv_packvirtual->fields[2]), 2); 
						$vqtdpack = $this->vrv_packvirtual->fields[3]; 
						$vunpack = $this->array_atac->fields[8]; 
					} 
					

					if($this->array_atac->fields[4] == " ")

					{

						$vprcat = round(($this->array_atac->fields[5] * $this->vrv_packvirtual->fields[2]), 2); 
						$vqtdat = $this->vrv_packvirtual->fields[3]; 
						$vunat = $this->vrv_packvirtual->fields[0]; 
					} 
				} 
			
			} 
		

			if(($this->array_atac->fields[9] > 0) || ($vprcpack == $vprcuni && $vprcat == $vprcuni) || ($vprcat == 0 && $vprcpack == 0) || ($vprcpack == 0 && $vprcat == $vprcuni))

			{	

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500170Preco"."\r"."\n";

				$msg  .="231100003500150Unitario"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="262200002300130".number_format($vprcuni, 2, ',', '.')."\r"."\n"; 
				$msg  .="E"."\r"."\n";					

			}

			elseif($vprcpack == 0)

			{

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500230Preco UN."."\r"."\n"; 

				$msg  .="231100003500210Atacado."."\r"."\n";

				$msg  .="231100003500190Emb. Fechada"."\r"."\n";

				$msg  .="231100003500140Preco"."\r"."\n";

				$msg  .="231100003500120Varejo"."\r"."\n";

				$msg  .="231100003500100Und. Avulsa"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="262200002300185".number_format($vprcat, 2, ',', '.')."\r"."\n";  
				$msg  .="262200002300085".number_format($vprcuni, 2, ',', '.')."\r"."\n";  
				$msg  .="212300003500040" . $veanat . " Emb com " . $vqtdat .  " R$ " . number_format($vtat, 2, ',', '.')."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";					

			}

			elseif(($vprcat == 0) || ($vprcat == $vprcuni))

			{

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265".$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="241200003700265Preco Qtd"."\r"."\n";

				$msg  .="231100003500210A partir de"."\r"."\n";

				$msg  .="2311000035001908" . $vqtdpack . " " . $vunpack ."\r"."\n"; 
				$msg  .="231100003500140Preco"."\r"."\n";

				$msg  .="231100003500120varejo"."\r"."\n";

				$msg  .="231100003500100Und. Avulsa"."\r"."\n";

				$msg  .="2F5302001700265".$this->array_atac->fields[4]."\r"."\n"; 
				$msg  .="262200002300185".number_format($vprcpack, 2, ',', '.')."\r"."\n";  
				$msg  .="262200002300085".number_format($vprcuni, 2, ',', '.')."\r"."\n";    
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";		

			}

			else
			{	

				$msg   ="<stx>L"."\r"."\n";

				$msg  .="D11"."\r"."\n";

				$msg  .="H13"."\r"."\n";

				$msg  .="PC"."\r"."\n";

				$msg  .="Q0001"."\r"."\n";

				$msg  .="241200003700265 " .$this->array_atac->fields[3]."\r"."\n"; 
				$msg  .="231100003500240Preco"."\r"."\n";

				$msg  .="231100003500220Atacado"."\r"."\n";

				$msg  .="231100003500200Emb. Fechada"."\r"."\n";

				$msg  .="231100003500170Preco Qtd"."\r"."\n";

				$msg  .="231100003500150A partir de"."\r"."\n";

				$msg  .="232100003500130" . $vqtdpack . " " . $vunpack ."\r"."\n"; 
				$msg  .="231100003500100Preco"."\r"."\n";

				$msg  .="231100003500080Varejo"."\r"."\n";

				$msg  .="231100003500060Und. Avulsa"."\r"."\n";

				$msg  .="255200002550195".number_format($vprcat, 2, ',', '.')."\r"."\n";  
				$msg  .="255200002550130".number_format($vprcpack, 2, ',', '.')."\r"."\n";  
				$msg  .="255200002550065".number_format($vprcuni, 2, ',', '.')."\r"."\n";    
				$msg  .="2F5302001700265 " .$this->array_atac->fields[4]. "\r"."\n"; 
				$msg  .="212300003500010 " . $veanat . " Emb com " . $vqtdat . " R$ " . number_format($vtat, 2, ',', '.') ."\r"."\n"; 
				$msg  .="221200001300040".$this->array_atac->fields[14]."\r"."\n"; 
				$msg  .="E"."\r"."\n";	
			}  
			$this->array_atac->MoveNext();		
		} 
		$this->array_atac->Close();

	}

}// FIM DA FUNÇÃO;

?>