/*
 * $Id: SphinxMatch.java 1172 2008-02-24 13:50:48Z shodan $
 */

package org.sphx.api;

import java.util.*;

/**
 * Matched document information, as in search result.
 */
public class SphinxMatch
{
	/** Matched document ID. */
	public long		docId;

	/** Matched document weight. */
	public int			weight;

	/** Matched document attribute values. */
	public ArrayList	attrValues;


	/** Trivial constructor. */
	public SphinxMatch ( long docId, int weight )
	{
		this.docId = docId;
		this.weight = weight;
		this.attrValues = new ArrayList();
	}
}

/*
 * $Id: SphinxMatch.java 1172 2008-02-24 13:50:48Z shodan $
 */
