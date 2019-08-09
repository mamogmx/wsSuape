-- Table: pe.e_tipopratica

ALTER TABLE pe.e_tipopratica ADD COLUMN classe varchar;

ALTER TABLE pe.e_intervento ADD COLUMN codice varchar;
ALTER TABLE pe.e_intervento ADD COLUMN nome varchar;
ALTER TABLE pe.e_intervento ADD COLUMN settore_padre varchar;
ALTER TABLE pe.e_intervento ADD COLUMN enabled integer DEFAULT 1;

CREATE TABLE pe.e_sportelli
(
  id serial primary key,
  codice varchar,
  nome varchar,
  descrizione text,
  codice_aoo varchar,
  codice_suape varchar,
  identificativo_suape varchar,
  enabled integer default 1,
  ordine integer default 0
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_sportelli
  OWNER TO postgres;


CREATE TABLE pe.e_settori
(
  id serial primary key,
  codice varchar,
  nome varchar,
  descrizione text,
  settore_padre varchar,
  enabled integer default 1,
  ordine integer default 0
)
WITH (
  OIDS=FALSE
);
ALTER TABLE pe.e_settori
  OWNER TO postgres;

DROP TABLE pe.e_intervento CASCADE;

CREATE TABLE pe.e_intervento
(
  id serial primary key,
  codice varchar,
  descrizione character varying,
  nome character varying,
  settore_padre character varying,
  cod_belfiore character varying,
  enabled integer DEFAULT 1,
  ordine integer DEFAULT 0
)
WITH (
  OIDS=TRUE
);







CREATE OR REPLACE VIEW pe.avvio_procedimento AS 
 SELECT avvioproc.id,
    avvioproc.pratica,
    avvioproc.numero,
    e_tipopratica.nome AS tipo_pratica,
    e_intervento.descrizione AS tipo_intervento,
    avvioproc.data_presentazione,
    avvioproc.protocollo,
    avvioproc.data_prot,
    avvioproc.protocollo_int,
    avvioproc.rif_pratica,
    avvioproc.data_prot_int,
    avvioproc.chk,
    users.nome AS nome_responsabile,
    avvioproc.data_resp,
    avvioproc.com_resp,
    avvioproc.data_com_resp,
    avvioproc.oggetto,
    avvioproc.note,
    ((e_diritti_segreteria.nome::text || COALESCE(' - '::text || e_diritti_segreteria.tipologia::text, ''::text)) || ' -  € '::text) || e_diritti_segreteria.importo::character varying::text AS diritti_segreteria,
    avvioproc.riduzione_diritti,
    avvioproc.pagamento_diritti
   FROM pe.avvioproc
     JOIN pe.e_tipopratica ON avvioproc.tipo = e_tipopratica.id
     LEFT JOIN pe.e_intervento ON avvioproc.intervento = e_intervento.id
     LEFT JOIN admin.users ON avvioproc.resp_proc = users.userid
     LEFT JOIN pe.e_diritti_segreteria ON avvioproc.diritti_segreteria = e_diritti_segreteria.id;

ALTER TABLE pe.avvio_procedimento
  OWNER TO postgres;


 
CREATE OR REPLACE VIEW pe.elenco_tipointervento AS 
 SELECT e_intervento.id,
    coalesce(nome,e_intervento.descrizione) AS opzione
   FROM pe.e_intervento
  ORDER BY e_intervento.ordine, coalesce(nome,e_intervento.descrizione);

ALTER TABLE pe.elenco_tipointervento
  OWNER TO postgres; 

 
CREATE OR REPLACE VIEW pe.ws_iol_avvioproc AS 
 SELECT a.pratica,
    a.numero,
    a.protocollo,
    a.data_prot,
    a.data_presentazione,
    b.nome AS tipo,
    COALESCE(h.descrizione, h.nome::text) AS categoria,
    c.descrizione AS intervento,
    btrim(format('%s %s %s'::text, COALESCE(d.app, ''::character varying), COALESCE(d.cognome, ''::character varying), COALESCE(d.nominativo, ''::character varying))) AS resp_proc,
    a.data_resp,
    btrim(format('%s %s %s'::text, COALESCE(e.app, ''::character varying), COALESCE(e.cognome, ''::character varying), COALESCE(e.nominativo, ''::character varying))) AS resp_it,
    a.data_resp_it,
    btrim(format('%s %s %s'::text, COALESCE(f.app, ''::character varying), COALESCE(f.cognome, ''::character varying), COALESCE(f.nominativo, ''::character varying))) AS resp_ia,
    a.data_resp_ia,
    btrim(format('%s %s %s'::text, COALESCE(g.app, ''::character varying), COALESCE(g.cognome, ''::character varying), COALESCE(g.nominativo, ''::character varying))) AS resp_amb,
    a.data_resp_amb,
    a.oggetto,
    a.note,
    a.data_chiusura,
    a.note_chiusura,
    a.data_chiusura_pa,
    a.note_chiusura_pa,
    a.pos_archivio,
    a.fascicolo,
    a.online,
    a.sportello,
    a.foreign_id,
    i.nome AS comune,
    a.anno
   FROM pe.avvioproc a
     LEFT JOIN pe.e_tipopratica b ON a.tipo = b.id
     LEFT JOIN pe.e_intervento c ON a.intervento = c.id
     LEFT JOIN admin.users d ON a.resp_proc = d.userid
     LEFT JOIN admin.users e ON a.resp_it = e.userid
     LEFT JOIN admin.users f ON a.resp_ia = f.userid
     LEFT JOIN admin.users g ON a.resp_amb = g.userid
     LEFT JOIN pe.e_categoriapratica h ON a.categoria = h.id
     LEFT JOIN admin.unione_comuni i ON a.cod_belfiore::text = i.codice::text
  ORDER BY a.pratica;

ALTER TABLE pe.ws_iol_avvioproc
  OWNER TO postgres;



CREATE OR REPLACE VIEW stp.single_pratica AS 
 SELECT a.pratica,
    a.numero,
    b.nome AS tipo_pratica,
    c.descrizione AS intervento,
    a.anno,
    a.data_presentazione,
    a.protocollo,
    a.data_prot AS data_protocollo,
    a.protocollo_int,
    a.data_prot_int,
    d.nome AS responsabile_procedimento,
    a.data_resp AS data_responsabile,
    a.com_resp AS protocollo_com_rdp,
    a.data_com_resp AS data_comunicazione_responsabile,
    e.nome AS istruttore_tecnico,
    a.data_resp_it AS data_responsabile_it,
    f.nome AS istruttore_amministrativo,
    a.data_resp_ia AS data_responsabile_ia,
    a.rif_aut_amb AS numero_autorizzazione_amb,
    a.oggetto,
    a.note,
    a.rif_pratica AS numero_pratica_precedente,
    a.diritti_segreteria,
    a.riduzione_diritti,
    a.pagamento_diritti
   FROM pe.avvioproc a
     LEFT JOIN pe.e_tipopratica b ON a.tipo = b.id
     LEFT JOIN pe.e_intervento c ON a.intervento = c.id
     LEFT JOIN admin.users d ON a.resp_proc = d.userid
     LEFT JOIN admin.users e ON a.resp_it = e.userid
     LEFT JOIN admin.users f ON a.resp_ia = f.userid;

ALTER TABLE stp.single_pratica
  OWNER TO postgres;
   