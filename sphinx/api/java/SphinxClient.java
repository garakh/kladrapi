/*
 * $Id: SphinxClient.java 3701 2013-02-20 18:10:18Z deogar $
 *
 * Java version of Sphinx searchd client (Java API)
 *
 * Copyright (c) 2007, Vladimir Fedorkov
 * Copyright (c) 2007-2013, Andrew Aksyonoff
 * Copyright (c) 2008-2013, Sphinx Technologies Inc
 * All rights reserved
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License. You should have
 * received a copy of the GPL license along with this program; if you
 * did not, you can find it at http://www.gnu.org/
 */

package org.sphx.api;

import java.io.*;
import java.net.*;
import java.util.*;
import java.net.SocketAddress.*;

/** Sphinx client class */
public class SphinxClient
{
	/* matching modes */
	public final static int SPH_MATCH_ALL			= 0;
	public final static int SPH_MATCH_ANY			= 1;
	public final static int SPH_MATCH_PHRASE		= 2;
	public final static int SPH_MATCH_BOOLEAN		= 3;
	public final static int SPH_MATCH_EXTENDED		= 4;
	public final static int SPH_MATCH_FULLSCAN		= 5;
	public final static int SPH_MATCH_EXTENDED2		= 6;

	/* ranking modes (extended2 only) */
	public final static int SPH_RANK_PROXIMITY_BM25	= 0;
	public final static int SPH_RANK_BM25			= 1;
	public final static int SPH_RANK_NONE			= 2;
	public final static int SPH_RANK_WORDCOUNT		= 3;
	public final static int SPH_RANK_PROXIMITY		= 4;
	public final static int SPH_RANK_MATCHANY		= 5;
	public final static int SPH_RANK_FIELDMASK		= 6;
	public final static int SPH_RANK_SPH04			= 7;
	public final static int SPH_RANK_EXPR			= 8;
	public final static int SPH_RANK_TOTAL			= 9;

	/* sorting modes */
	public final static int SPH_SORT_RELEVANCE		= 0;
	public final static int SPH_SORT_ATTR_DESC		= 1;
	public final static int SPH_SORT_ATTR_ASC		= 2;
	public final static int SPH_SORT_TIME_SEGMENTS	= 3;
	public final static int SPH_SORT_EXTENDED		= 4;
	public final static int SPH_SORT_EXPR			= 5;

	/* grouping functions */
	public final static int SPH_GROUPBY_DAY			= 0;
	public final static int SPH_GROUPBY_WEEK		= 1;
	public final static int SPH_GROUPBY_MONTH		= 2;
	public final static int SPH_GROUPBY_YEAR		= 3;
	public final static int SPH_GROUPBY_ATTR		= 4;
	public final static int SPH_GROUPBY_ATTRPAIR	= 5;

	/* searchd reply status codes */
	public final static int SEARCHD_OK				= 0;
	public final static int SEARCHD_ERROR			= 1;
	public final static int SEARCHD_RETRY			= 2;
	public final static int SEARCHD_WARNING			= 3;

	/* attribute types */
	public final static int SPH_ATTR_INTEGER		= 1;
	public final static int SPH_ATTR_TIMESTAMP		= 2;
	public final static int SPH_ATTR_ORDINAL		= 3;
	public final static int SPH_ATTR_BOOL			= 4;
	public final static int SPH_ATTR_FLOAT			= 5;
	public final static int SPH_ATTR_BIGINT			= 6;
	public final static int SPH_ATTR_STRING			= 7;
	public final static int SPH_ATTR_MULTI			= 0x40000001;
	public final static int SPH_ATTR_MULTI64		= 0x40000002;

	/* searchd commands */
	private final static int SEARCHD_COMMAND_SEARCH		= 0;
	private final static int SEARCHD_COMMAND_EXCERPT	= 1;
	private final static int SEARCHD_COMMAND_UPDATE		= 2;
	private final static int SEARCHD_COMMAND_KEYWORDS	= 3;
	private final static int SEARCHD_COMMAND_PERSIST	= 4;
	private final static int SEARCHD_COMMAND_FLUSHATTRS	= 7;

	/* searchd command versions */
	private final static int VER_MAJOR_PROTO		= 0x1;
	private final static int VER_COMMAND_SEARCH		= 0x119;
	private final static int VER_COMMAND_EXCERPT	= 0x102;
	private final static int VER_COMMAND_UPDATE		= 0x103;
	private final static int VER_COMMAND_KEYWORDS	= 0x100;
	private final static int VER_COMMAND_FLUSHATTRS	= 0x100;

	/* filter types */
	private final static int SPH_FILTER_VALUES		= 0;
	private final static int SPH_FILTER_RANGE		= 1;
	private final static int SPH_FILTER_FLOATRANGE	= 2;


	private String		_host;
	private int			_port;
	private String		_path;
	private Socket		_socket;

	private int			_offset;
	private int			_limit;
	private int			_mode;
	private int[]		_weights;
	private int			_sort;
	private String		_sortby;
	private int			_minId;
	private int			_maxId;
	private ByteArrayOutputStream	_rawFilters;
	private DataOutputStream		_filters;
	private int			_filterCount;
	private String		_groupBy;
	private int			_groupFunc;
	private String		_groupSort;
	private String		_groupDistinct;
	private int			_maxMatches;
	private int			_cutoff;
	private int			_retrycount;
	private int			_retrydelay;
	private String		_latitudeAttr;
	private String		_longitudeAttr;
	private float		_latitude;
	private float		_longitude;

	private String		_error;
	private String		_warning;
	private boolean		_connerror;
	private int			_timeout;

	private ArrayList	_reqs;
	private Map			_indexWeights;
	private int			_ranker;
	private String		_rankexpr;
	private int			_maxQueryTime;
	private Map			_fieldWeights;
	private Map			_overrideTypes;
	private Map			_overrideValues;
	private String		_select;

	/** Creates a new SphinxClient instance. */
	public SphinxClient()
	{
		this("localhost", 9312);
	}

	/** Creates a new SphinxClient instance, with host:port specification. */
	public SphinxClient(String host, int port)
	{
		_host	= host;
		_port	= port;
		_path	= null;
		_socket	= null;

		_offset	= 0;
		_limit	= 20;
		_mode	= SPH_MATCH_ALL;
		_sort	= SPH_SORT_RELEVANCE;
		_sortby	= "";
		_minId	= 0;
		_maxId	= 0;

		_filterCount	= 0;
		_rawFilters		= new ByteArrayOutputStream();
		_filters		= new DataOutputStream(_rawFilters);

		_groupBy		= "";
		_groupFunc		= SPH_GROUPBY_DAY;
		_groupSort		= "@group desc";
		_groupDistinct	= "";

		_maxMatches		= 1000;
		_cutoff			= 0;
		_retrycount		= 0;
		_retrydelay		= 0;

		_latitudeAttr	= null;
		_longitudeAttr	= null;
		_latitude		= 0;
		_longitude		= 0;

		_error			= "";
		_warning		= "";
		_connerror		= false;
		_timeout		= 1000;

		_reqs			= new ArrayList();
		_weights		= null;
		_indexWeights	= new LinkedHashMap();
		_fieldWeights	= new LinkedHashMap();
		_ranker			= SPH_RANK_PROXIMITY_BM25;
		_rankexpr		= "";

		_overrideTypes	= new LinkedHashMap();
		_overrideValues	= new LinkedHashMap();
		_select			= "*";
	}

	/** Get last error message, if any. */
	public String GetLastError()
	{
		return _error;
	}

	/** Get last warning message, if any. */
	public String GetLastWarning()
	{
		return _warning;
	}

	/** Get last error flag (to tell network connection errors from searchd errors or broken responses). */
	public boolean IsConnectError()
	{
		return _connerror;
	}

	/** Set searchd host and port to connect to. */
	public void SetServer(String host, int port) throws SphinxException
	{
		myAssert ( host!=null && host.length()>0, "host name must not be empty" );
		myAssert ( port>0 && port<65536, "port must be in 1..65535 range" );
		_host = host;
		_port = port;
	}

	/** Set server connection timeout (0 to remove), in milliseconds. */
	public void SetConnectTimeout ( int timeout )
	{
		_timeout = Math.max ( timeout, 0 );
	}

	/** Internal method. Sanity check. */
	private void myAssert ( boolean condition, String err ) throws SphinxException
	{
		if ( !condition )
		{
			_error = err;
			throw new SphinxException ( err );
		}
	}

	/** Internal method. String IO helper. */
	private static void writeNetUTF8 ( DataOutputStream ostream, String str ) throws IOException
	{
		if ( str==null )
		{
			ostream.writeInt ( 0 );
			return;
		}

		byte[] sBytes = str.getBytes ( "UTF-8" );
		int iLen = sBytes.length;

		ostream.writeInt ( iLen );
		ostream.write ( sBytes );
	}

	/** Internal method. String IO helper. */
	private static String readNetUTF8(DataInputStream istream) throws IOException
	{
		int iLen = istream.readInt();
		byte[] sBytes = new byte [ iLen ];
		istream.readFully ( sBytes );
		return new String ( sBytes, "UTF-8");
	}

	/** Internal method. Unsigned int IO helper. */
	private static long readDword ( DataInputStream istream ) throws IOException
	{
		long v = (long) istream.readInt ();
		if ( v<0 )
			v += 4294967296L;
		return v;
	}

	/** Internal method. Connect to searchd and exchange versions. */
	private Socket _Connect()
	{
		if ( _socket!=null )
			return _socket;

		_connerror = false;
		Socket sock = null;
		try
		{
			sock = new Socket ();
			sock.setSoTimeout ( _timeout );
			InetSocketAddress addr = new InetSocketAddress ( _host, _port );
			sock.connect ( addr, _timeout );
			
			DataInputStream sIn = new DataInputStream ( sock.getInputStream() );
			int version = sIn.readInt();
			if ( version<1 )
			{
				sock.close ();
				_error = "expected searchd protocol version 1+, got version " + version;
				return null;
			}

			DataOutputStream sOut = new DataOutputStream ( sock.getOutputStream() );
			sOut.writeInt ( VER_MAJOR_PROTO );

		} catch ( IOException e )
		{
			_error = "connection to " + _host + ":" + _port + " failed: " + e;
			_connerror = true;

			try
			{
				if ( sock!=null )
					sock.close ();
			} catch ( IOException e1 ) {}
			return null;
		}

		return sock;
	}

	/** Internal method. Get and check response packet from searchd. */
	private byte[] _GetResponse ( Socket sock )
	{
		/* connect */
		DataInputStream sIn = null;
		InputStream SockInput = null;
		try
		{
			SockInput = sock.getInputStream();
			sIn = new DataInputStream ( SockInput );

		} catch ( IOException e )
		{
			_error = "getInputStream() failed: " + e;
			return null;
		}

		/* read response */
		byte[] response = null;
		short status = 0, ver = 0;
		int len = 0;
		try
		{
			/* read status fields */
			status = sIn.readShort();
			ver = sIn.readShort();
			len = sIn.readInt();

			/* read response if non-empty */
			if ( len<=0 )
			{
				_error = "invalid response packet size (len=" + len + ")";
				return null;
			}

			response = new byte[len];
			sIn.readFully ( response, 0, len );

			/* check status */
			if ( status==SEARCHD_WARNING )
			{
				DataInputStream in = new DataInputStream ( new ByteArrayInputStream ( response ) );

				int iWarnLen = in.readInt ();
				_warning = new String ( response, 4, iWarnLen );

				System.arraycopy ( response, 4+iWarnLen, response, 0, response.length-4-iWarnLen );

			} else if ( status==SEARCHD_ERROR )
			{
				_error = "searchd error: " + new String ( response, 4, response.length-4 );
				return null;

			} else if ( status==SEARCHD_RETRY )
			{
				_error = "temporary searchd error: " + new String ( response, 4, response.length-4 );
				return null;

			} else if ( status!=SEARCHD_OK )
			{
				_error = "searched returned unknown status, code=" + status;
				return null;
			}

		} catch ( IOException e )
		{
			if ( len!=0 )
			{
				/* get trace, to provide even more failure details */
				PrintWriter ew = new PrintWriter ( new StringWriter() );
				e.printStackTrace ( ew );
				ew.flush ();
				ew.close ();
				String sTrace = ew.toString ();

				/* build error message */
				_error = "failed to read searchd response (status=" + status + ", ver=" + ver + ", len=" + len + ", trace=" + sTrace +")";
			} else
			{
				_error = "received zero-sized searchd response (searchd crashed?): " + e.getMessage();
			}
			return null;

		} finally
		{
			if ( _socket==null )
			{
				try
				{
					if ( sIn!=null )
						sIn.close();
					if ( sock!=null && !sock.isConnected() )
						sock.close();
				} catch ( IOException e )
				{
					/* silently ignore close failures; nothing could be done anyway */
				}
			}
		}

		return response;
	}

	/** Internal method. Connect to searchd, send request, get response as DataInputStream. */
	private DataInputStream _DoRequest ( int command, int version, ByteArrayOutputStream req )
	{
		/* connect */
		Socket sock = _Connect();
		if ( sock==null )
			return null;

		/* send request */
	   	byte[] reqBytes = req.toByteArray();
	   	try
	   	{
			DataOutputStream sockDS = new DataOutputStream ( sock.getOutputStream() );
			sockDS.writeShort ( command );
			sockDS.writeShort ( version );
			sockDS.writeInt ( reqBytes.length );
			sockDS.write ( reqBytes );

		} catch ( Exception e )
		{
			_error = "network error: " + e;
			_connerror = true;
			return null;
		}

		/* get response */
		byte[] response = _GetResponse ( sock );
		if ( response==null )
			return null;

		/* spawn that tampon */
		return new DataInputStream ( new ByteArrayInputStream ( response ) );
	}

	/** Set matches offset and limit to return to client, max matches to retrieve on server, and cutoff. */
	public void SetLimits ( int offset, int limit, int max, int cutoff ) throws SphinxException
	{
		myAssert ( offset>=0, "offset must not be negative" );
		myAssert ( limit>0, "limit must be positive" );
		myAssert ( max>0, "max must be positive" );
		myAssert ( cutoff>=0, "cutoff must not be negative" );

		_offset = offset;
		_limit = limit;
		_maxMatches = max;
		_cutoff = cutoff;
	}

	/** Set matches offset and limit to return to client, and max matches to retrieve on server. */
	public void SetLimits ( int offset, int limit, int max ) throws SphinxException
	{
		SetLimits ( offset, limit, max, _cutoff );
	}

	/** Set matches offset and limit to return to client. */
	public void SetLimits ( int offset, int limit) throws SphinxException
	{
		SetLimits ( offset, limit, _maxMatches, _cutoff );
	}

	/** Set maximum query time, in milliseconds, per-index, 0 means "do not limit". */
	public void SetMaxQueryTime ( int maxTime ) throws SphinxException
	{
		myAssert ( maxTime>=0, "max_query_time must not be negative" );
		_maxQueryTime = maxTime;
	}

	/** Set matching mode. */
	public void SetMatchMode(int mode) throws SphinxException
	{
		myAssert (
			mode==SPH_MATCH_ALL ||
			mode==SPH_MATCH_ANY ||
			mode==SPH_MATCH_PHRASE ||
			mode==SPH_MATCH_BOOLEAN ||
			mode==SPH_MATCH_EXTENDED ||
			mode==SPH_MATCH_FULLSCAN ||
			mode==SPH_MATCH_EXTENDED2, "unknown mode value; use one of the SPH_MATCH_xxx constants" );
		_mode = mode;
	}

	/** Set ranking mode. */
	public void SetRankingMode ( int ranker, String rankexpr ) throws SphinxException
	{
		myAssert ( ranker>=0 && ranker<SPH_RANK_TOTAL, "unknown ranker value; use one of the SPH_RANK_xxx constants" );
		_rankexpr = ( rankexpr==null ) ? "" : rankexpr;
		_ranker = ranker;
	}

	/** Set sorting mode. */
	public void SetSortMode ( int mode, String sortby ) throws SphinxException
	{
		myAssert (
			mode==SPH_SORT_RELEVANCE ||
			mode==SPH_SORT_ATTR_DESC ||
			mode==SPH_SORT_ATTR_ASC ||
			mode==SPH_SORT_TIME_SEGMENTS ||
			mode==SPH_SORT_EXTENDED ||
			mode==SPH_SORT_EXPR, "unknown mode value; use one of the available SPH_SORT_xxx constants" );
		myAssert ( mode==SPH_SORT_RELEVANCE || ( sortby!=null && sortby.length()>0 ), "sortby string must not be empty in selected mode" );

		_sort = mode;
		_sortby = ( sortby==null ) ? "" : sortby;
	}

	/** Set per-field weights (all values must be positive). WARNING: DEPRECATED, use SetFieldWeights() instead. */
	public void SetWeights(int[] weights) throws SphinxException
	{
		myAssert ( weights!=null, "weights must not be null" );
		for (int i = 0; i < weights.length; i++) {
			int weight = weights[i];
			myAssert ( weight>0, "all weights must be greater than 0" );
		}
		_weights = weights;
	}

	/**
	 * Bind per-field weights by field name.
	 * @param fieldWeights hash which maps String index names to Integer weights
	 */
	public void SetFieldWeights ( Map fieldWeights ) throws SphinxException
	{
		/* FIXME! implement checks here */
		_fieldWeights = ( fieldWeights==null ) ? new LinkedHashMap () : fieldWeights;
	}

	/**
	 * Bind per-index weights by index name (and enable summing the weights on duplicate matches, instead of replacing them).
	 * @param indexWeights hash which maps String index names to Integer weights
	 */
	public void SetIndexWeights ( Map indexWeights ) throws SphinxException
	{
		/* FIXME! implement checks here */
		_indexWeights = ( indexWeights==null ) ? new LinkedHashMap () : indexWeights;
	}

	/** Set document IDs range to match. */
	public void SetIDRange ( int min, int max ) throws SphinxException
	{
		myAssert ( min<=max, "min must be less or equal to max" );
		_minId = min;
		_maxId = max;
	}

	/** Set values filter. Only match records where attribute value is in given set. */
	public void SetFilter ( String attribute, int[] values, boolean exclude ) throws SphinxException
	{
		myAssert ( values!=null && values.length>0, "values array must not be null or empty" );
		myAssert ( attribute!=null && attribute.length()>0, "attribute name must not be null or empty" );

		try
		{
			writeNetUTF8 ( _filters, attribute );
			_filters.writeInt ( SPH_FILTER_VALUES );
			_filters.writeInt ( values.length );
			for ( int i=0; i<values.length; i++ )
				_filters.writeLong ( values[i] );
			_filters.writeInt ( exclude ? 1 : 0 );

		} catch ( Exception e )
		{
			myAssert ( false, "IOException: " + e.getMessage() );
		}
		_filterCount++;
	}

	/** Set values filter. Only match records where attribute value is in given set. */
	public void SetFilter ( String attribute, long[] values, boolean exclude ) throws SphinxException
	{
		myAssert ( values!=null && values.length>0, "values array must not be null or empty" );
		myAssert ( attribute!=null && attribute.length()>0, "attribute name must not be null or empty" );

		try
		{
			writeNetUTF8 ( _filters, attribute );
			_filters.writeInt ( SPH_FILTER_VALUES );
			_filters.writeInt ( values.length );
			for ( int i=0; i<values.length; i++ )
				_filters.writeLong ( values[i] );
			_filters.writeInt ( exclude ? 1 : 0 );

		} catch ( Exception e )
		{
			myAssert ( false, "IOException: " + e.getMessage() );
		}
		_filterCount++;
	}

	/** Set values filter with a single value (syntax sugar; see {@link #SetFilter(String,int[],boolean)}). */
	public void SetFilter ( String attribute, int value, boolean exclude ) throws SphinxException
	{
		long[] values = new long[] { value };
		SetFilter ( attribute, values, exclude );
	}

	/** Set values filter with a single value (syntax sugar; see {@link #SetFilter(String,int[],boolean)}). */
	public void SetFilter ( String attribute, long value, boolean exclude ) throws SphinxException
	{
		long[] values = new long[] { value };
		SetFilter ( attribute, values, exclude );
	}

	/** Set integer range filter.  Only match records if attribute value is beetwen min and max (inclusive). */
	public void SetFilterRange ( String attribute, long min, long max, boolean exclude ) throws SphinxException
	{
		myAssert ( min<=max, "min must be less or equal to max" );
		try
		{
			writeNetUTF8 ( _filters, attribute );
			_filters.writeInt ( SPH_FILTER_RANGE );
			_filters.writeLong ( min );
			_filters.writeLong ( max );
			_filters.writeInt ( exclude ? 1 : 0 );

		} catch ( Exception e )
		{
			myAssert ( false, "IOException: " + e.getMessage() );
		}
		_filterCount++;
	}

	/** Set integer range filter.  Only match records if attribute value is beetwen min and max (inclusive). */
	public void SetFilterRange ( String attribute, int min, int max, boolean exclude ) throws SphinxException
	{
		SetFilterRange ( attribute, (long)min, (long)max, exclude );
	}

	/** Set float range filter.  Only match records if attribute value is beetwen min and max (inclusive). */
	public void SetFilterFloatRange ( String attribute, float min, float max, boolean exclude ) throws SphinxException
	{
		myAssert ( min<=max, "min must be less or equal to max" );
		try
		{
			writeNetUTF8 ( _filters, attribute );
			_filters.writeInt ( SPH_FILTER_FLOATRANGE );
			_filters.writeFloat ( min );
			_filters.writeFloat ( max );
			_filters.writeInt ( exclude ? 1 : 0 );
		} catch ( Exception e )
		{
			myAssert ( false, "IOException: " + e.getMessage() );
		}
		_filterCount++;
	}

	/** Setup geographical anchor point. Required to use @geodist in filters and sorting; distance will be computed to this point. */
	public void SetGeoAnchor ( String latitudeAttr, String longitudeAttr, float latitude, float longitude ) throws SphinxException
	{
		myAssert ( latitudeAttr!=null && latitudeAttr.length()>0, "longitudeAttr string must not be null or empty" );
		myAssert ( longitudeAttr!=null && longitudeAttr.length()>0, "longitudeAttr string must not be null or empty" );

		_latitudeAttr = latitudeAttr;
		_longitudeAttr = longitudeAttr;
		_latitude = latitude;
		_longitude = longitude;
	}

	/** Set grouping attribute and function. */
	public void SetGroupBy ( String attribute, int func, String groupsort ) throws SphinxException
	{
		myAssert (
			func==SPH_GROUPBY_DAY ||
			func==SPH_GROUPBY_WEEK ||
			func==SPH_GROUPBY_MONTH ||
			func==SPH_GROUPBY_YEAR ||
			func==SPH_GROUPBY_ATTR ||
			func==SPH_GROUPBY_ATTRPAIR, "unknown func value; use one of the available SPH_GROUPBY_xxx constants" );

		_groupBy = attribute;
		_groupFunc = func;
		_groupSort = groupsort;
	}

	/** Set grouping attribute and function with default ("@group desc") groupsort (syntax sugar). */
	public void SetGroupBy(String attribute, int func) throws SphinxException
	{
		SetGroupBy(attribute, func, "@group desc");
	}

	/** Set count-distinct attribute for group-by queries. */
	public void SetGroupDistinct(String attribute)
	{
		_groupDistinct = attribute;
	}

	/** Set distributed retries count and delay. */
	public void SetRetries ( int count, int delay ) throws SphinxException
	{
		myAssert ( count>=0, "count must not be negative" );
		myAssert ( delay>=0, "delay must not be negative" );
		_retrycount = count;
		_retrydelay = delay;
	}

	/** Set distributed retries count with default (zero) delay (syntax sugar). */
	public void SetRetries ( int count ) throws SphinxException
	{
		SetRetries ( count, 0 );
	}

	/**
	 * Set attribute values override (one override list per attribute).
	 * @param values maps Long document IDs to Int/Long/Float values (as specified in attrtype).
	 */
	public void SetOverride ( String attrname, int attrtype, Map values ) throws SphinxException
	{
		myAssert ( attrname!=null && attrname.length()>0, "attrname must not be empty" );
		myAssert ( attrtype==SPH_ATTR_INTEGER || attrtype==SPH_ATTR_TIMESTAMP || attrtype==SPH_ATTR_BOOL || attrtype==SPH_ATTR_FLOAT || attrtype==SPH_ATTR_BIGINT,
			"unsupported attrtype (must be one of INTEGER, TIMESTAMP, BOOL, FLOAT, or BIGINT)" );
		_overrideTypes.put ( attrname, new Integer ( attrtype ) );
		_overrideValues.put ( attrname, values );
	}

	/** Set select-list (attributes or expressions), SQL-like syntax. */
	public void SetSelect ( String select ) throws SphinxException
	{
		myAssert ( select!=null, "select clause string must not be null" );
		_select = select;
	}



	/** Reset all currently set filters (for multi-queries). */
	public void ResetFilters()
	{
		/* should we close them first? */
		_rawFilters = new ByteArrayOutputStream();
		_filters = new DataOutputStream(_rawFilters);
		_filterCount = 0;

		/* reset GEO anchor */
		_latitudeAttr = null;
		_longitudeAttr = null;
		_latitude = 0;
		_longitude = 0;
	}

	/** Clear groupby settings (for multi-queries). */
	public void ResetGroupBy ()
	{
		_groupBy = "";
		_groupFunc = SPH_GROUPBY_DAY;
		_groupSort = "@group desc";
		_groupDistinct = "";
	}

	/** Clear all attribute value overrides (for multi-queries). */
	public void ResetOverrides ()
    {
		_overrideTypes.clear ();
		_overrideValues.clear ();
    }



	/** Connect to searchd server and run current search query against all indexes (syntax sugar). */
	public SphinxResult Query ( String query ) throws SphinxException
	{
		return Query ( query, "*", "" );
	}

	/** Connect to searchd server and run current search query against all indexes (syntax sugar). */
	public SphinxResult Query ( String query, String index ) throws SphinxException
	{
		return Query ( query, index, "" );
	}

	/** Connect to searchd server and run current search query. */
	public SphinxResult Query ( String query, String index, String comment ) throws SphinxException
	{
		myAssert ( _reqs==null || _reqs.size()==0, "AddQuery() and Query() can not be combined; use RunQueries() instead" );

		AddQuery ( query, index, comment );
		SphinxResult[] results = RunQueries();
		_reqs = new ArrayList(); /* just in case it failed too early */
		if ( results==null || results.length<1 )
			return null; /* probably network error; error message should be already filled */

		SphinxResult res = results[0];
		_warning = res.warning;
		_error = res.error;
		if ( res==null || res.getStatus()==SEARCHD_ERROR )
			return null;
		return res;
	}

	/** Add new query with current settings to current search request. */
	public int AddQuery ( String query, String index, String comment ) throws SphinxException
	{
		ByteArrayOutputStream req = new ByteArrayOutputStream();

		/* build request */
		try {
			DataOutputStream out = new DataOutputStream(req);
			out.writeInt(_offset);
			out.writeInt(_limit);
			out.writeInt(_mode);
			out.writeInt(_ranker);
			if ( _ranker == SPH_RANK_EXPR ) {
				writeNetUTF8(out, _rankexpr);
			}
			out.writeInt(_sort);
			writeNetUTF8(out, _sortby);
			writeNetUTF8(out, query);
			int weightLen = _weights != null ? _weights.length : 0;

			out.writeInt(weightLen);
			if (_weights != null) {
				for (int i = 0; i < _weights.length; i++)
					out.writeInt(_weights[i]);
			}

			writeNetUTF8(out, index);
			out.writeInt(0);
			out.writeInt(_minId);
			out.writeInt(_maxId);

			/* filters */
			out.writeInt(_filterCount);
			out.write(_rawFilters.toByteArray());

			/* group-by, max matches, sort-by-group flag */
			out.writeInt(_groupFunc);
			writeNetUTF8(out, _groupBy);
			out.writeInt(_maxMatches);
			writeNetUTF8(out, _groupSort);

			out.writeInt(_cutoff);
			out.writeInt(_retrycount);
			out.writeInt(_retrydelay);

			writeNetUTF8(out, _groupDistinct);

			/* anchor point */
			if (_latitudeAttr == null || _latitudeAttr.length() == 0 || _longitudeAttr == null || _longitudeAttr.length() == 0) {
				out.writeInt(0);
			} else {
				out.writeInt(1);
				writeNetUTF8(out, _latitudeAttr);
				writeNetUTF8(out, _longitudeAttr);
				out.writeFloat(_latitude);
				out.writeFloat(_longitude);

			}

			/* per-index weights */
			out.writeInt(_indexWeights.size());
			for (Iterator e = _indexWeights.keySet().iterator(); e.hasNext();) {
				String indexName = (String) e.next();
				Integer weight = (Integer) _indexWeights.get(indexName);
				writeNetUTF8(out, indexName);
				out.writeInt(weight.intValue());
			}

			/* max query time */
			out.writeInt ( _maxQueryTime );

			/* per-field weights */
			out.writeInt ( _fieldWeights.size() );
			for ( Iterator e=_fieldWeights.keySet().iterator(); e.hasNext(); )
			{
				String field = (String) e.next();
				Integer weight = (Integer) _fieldWeights.get ( field );
				writeNetUTF8 ( out, field );
				out.writeInt ( weight.intValue() );
			}

			/* comment */
			writeNetUTF8 ( out, comment );

			/* overrides */
			out.writeInt ( _overrideTypes.size() );
			for ( Iterator e=_overrideTypes.keySet().iterator(); e.hasNext(); )
			{
				String attr = (String) e.next();
				Integer type = (Integer) _overrideTypes.get ( attr );
				Map values = (Map) _overrideValues.get ( attr );

				writeNetUTF8 ( out, attr );
				out.writeInt ( type.intValue() );
				out.writeInt ( values.size() );

				for ( Iterator e2=values.keySet().iterator(); e2.hasNext(); )
				{
					Long id = (Long) e2.next ();
					out.writeLong ( id.longValue() );
					switch ( type.intValue() )
					{
						case SPH_ATTR_FLOAT:	out.writeFloat ( ( (Float) values.get ( id ) ).floatValue() ); break;
						case SPH_ATTR_BIGINT:	out.writeLong ( ( (Long)values.get ( id ) ).longValue() ); break;
						default:				out.writeInt ( ( (Integer)values.get ( id ) ).intValue() ); break;
					}
				}
			}

			/* select-list */
			writeNetUTF8 ( out, _select );

			/* done! */
			out.flush ();
			int qIndex = _reqs.size();
			_reqs.add ( qIndex, req.toByteArray() );
			return qIndex;

		} catch ( Exception e )
		{
			myAssert ( false, "error in AddQuery(): " + e + ": " + e.getMessage() );

		} finally
		{
			try
			{
				_filters.close ();
				_rawFilters.close ();
			} catch ( IOException e )
			{
				myAssert ( false, "error in AddQuery(): " + e + ": " + e.getMessage() );
			}
		}
		return -1;
	}

	/** Run all previously added search queries. */
	public SphinxResult[] RunQueries() throws SphinxException
	{
		if ( _reqs==null || _reqs.size()<1 )
		{
			_error = "no queries defined, issue AddQuery() first";
			return null;
		}

		/* build the mega-request */
		int nreqs = _reqs.size();
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();
		try
		{
			DataOutputStream req = new DataOutputStream ( reqBuf );
			/* its a client */
			req.writeInt(0);
			req.writeInt ( nreqs );
			for ( int i=0; i<nreqs; i++ )
				req.write ( (byte[]) _reqs.get(i) );
			req.flush ();

		} catch ( Exception e )
		{
			_error = "internal error: failed to build request: " + e;
			return null;
		}

		DataInputStream in =_DoRequest ( SEARCHD_COMMAND_SEARCH, VER_COMMAND_SEARCH, reqBuf );
		if ( in==null )
			return null;

		SphinxResult[] results = new SphinxResult [ nreqs ];
		_reqs = new ArrayList();

		try
		{
			for ( int ires=0; ires<nreqs; ires++ )
			{
				SphinxResult res = new SphinxResult();
				results[ires] = res;

				int status = in.readInt();
				res.setStatus ( status );
				if (status != SEARCHD_OK) {
					String message = readNetUTF8(in);
					if (status == SEARCHD_WARNING) {
						res.warning = message;
					} else {
						res.error = message;
						continue;
					}
				}

				/* read fields */
				int nfields = in.readInt();
				res.fields = new String[nfields];
				int pos = 0;
				for (int i = 0; i < nfields; i++)
					res.fields[i] = readNetUTF8(in);

				/* read arrts */
				int nattrs = in.readInt();
				res.attrTypes = new int[nattrs];
				res.attrNames = new String[nattrs];
				for (int i = 0; i < nattrs; i++) {
					String AttrName = readNetUTF8(in);
					int AttrType = in.readInt();
					res.attrNames[i] = AttrName;
					res.attrTypes[i] = AttrType;
				}

				/* read match count */
				int count = in.readInt();
				int id64 = in.readInt();
				res.matches = new SphinxMatch[count];
				for ( int matchesNo=0; matchesNo<count; matchesNo++ )
				{
					SphinxMatch docInfo;
					docInfo = new SphinxMatch (
							( id64==0 ) ? readDword(in) : in.readLong(),
							in.readInt() );

					/* read matches */
					for (int attrNumber = 0; attrNumber < res.attrTypes.length; attrNumber++)
					{
						String attrName = res.attrNames[attrNumber];
						int type = res.attrTypes[attrNumber];

						/* handle bigints */
						if ( type==SPH_ATTR_BIGINT )
						{
							docInfo.attrValues.add ( attrNumber, new Long ( in.readLong() ) );
							continue;
						}

						/* handle floats */
						if ( type==SPH_ATTR_FLOAT )
						{
							docInfo.attrValues.add ( attrNumber, new Float ( in.readFloat() ) );
							continue;
						}

						/* handle strings */
						if ( type==SPH_ATTR_STRING )
						{
							String s = readNetUTF8(in);
							docInfo.attrValues.add ( attrNumber, s );
							continue;
						}

						/* handle everything else as unsigned ints */
						long val = readDword ( in );
						if ( type==SPH_ATTR_MULTI )
						{
							long[] vals = new long [ (int)val ];
							for ( int k=0; k<val; k++ )
								vals[k] = readDword ( in );

							docInfo.attrValues.add ( attrNumber, vals );
							
						} else if ( type==SPH_ATTR_MULTI64 )
						{
							val = val / 2;
							long[] vals = new long [ (int)val ];
							for ( int k=0; k<val; k++ )
								vals[k] = in.readLong ();

							docInfo.attrValues.add ( attrNumber, vals );
							
						} else
						{
							docInfo.attrValues.add ( attrNumber, new Long ( val ) );
						}
					}
					res.matches[matchesNo] = docInfo;
				}

				res.total = in.readInt();
				res.totalFound = in.readInt();
				res.time = in.readInt() / 1000.0f;

				res.words = new SphinxWordInfo [ in.readInt() ];
				for ( int i=0; i<res.words.length; i++ )
					res.words[i] = new SphinxWordInfo ( readNetUTF8(in), readDword(in), readDword(in) );
			}
			return results;

		} catch ( IOException e )
		{
			_error = "incomplete reply";
			return null;
		}
	}



	/**
	 * Connect to searchd server and generate excerpts (snippets) from given documents.
	 * @param opts maps String keys to String or Integer values (see the documentation for complete keys list).
	 * @return null on failure, array of snippets on success.
	 */
	public String[] BuildExcerpts ( String[] docs, String index, String words, Map opts ) throws SphinxException
	{
		myAssert(docs != null && docs.length > 0, "BuildExcerpts: Have no documents to process");
		myAssert(index != null && index.length() > 0, "BuildExcerpts: Have no index to process documents");
		myAssert(words != null && words.length() > 0, "BuildExcerpts: Have no words to highlight");
		if (opts == null) opts = new LinkedHashMap();

		/* fixup options */
		if (!opts.containsKey("before_match")) opts.put("before_match", "<b>");
		if (!opts.containsKey("after_match")) opts.put("after_match", "</b>");
		if (!opts.containsKey("chunk_separator")) opts.put("chunk_separator", "...");
		if (!opts.containsKey("html_strip_mode")) opts.put("html_strip_mode", "index");
		if (!opts.containsKey("limit")) opts.put("limit", new Integer(256));
		if (!opts.containsKey("limit_passages")) opts.put("limit_passages", new Integer(0));
		if (!opts.containsKey("limit_words")) opts.put("limit_words", new Integer(0));
		if (!opts.containsKey("around")) opts.put("around", new Integer(5));
		if (!opts.containsKey("start_passage_id")) opts.put("start_passage_id", new Integer(1));
		if (!opts.containsKey("exact_phrase")) opts.put("exact_phrase", new Integer(0));
		if (!opts.containsKey("single_passage")) opts.put("single_passage", new Integer(0));
		if (!opts.containsKey("use_boundaries")) opts.put("use_boundaries", new Integer(0));
		if (!opts.containsKey("weight_order")) opts.put("weight_order", new Integer(0));
		if (!opts.containsKey("load_files")) opts.put("load_files", new Integer(0));
		if (!opts.containsKey("allow_empty")) opts.put("allow_empty", new Integer(0));
		if (!opts.containsKey("query_mode")) opts.put("query_mode", new Integer(0));
		if (!opts.containsKey("force_all_words")) opts.put("force_all_words", new Integer(0));

		/* build request */
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();
		DataOutputStream req = new DataOutputStream ( reqBuf );
		try
		{
			req.writeInt(0);
			int iFlags = 1; /* remove_spaces */
			if ( ((Integer)opts.get("exact_phrase")).intValue()!=0 )	iFlags |= 2;
			if ( ((Integer)opts.get("single_passage")).intValue()!=0 )	iFlags |= 4;
			if ( ((Integer)opts.get("use_boundaries")).intValue()!=0 )	iFlags |= 8;
			if ( ((Integer)opts.get("weight_order")).intValue()!=0 )	iFlags |= 16;
			if ( ((Integer)opts.get("query_mode")).intValue()!=0 )		iFlags |= 32;
			if ( ((Integer)opts.get("force_all_words")).intValue()!=0 )	iFlags |= 64;
			if ( ((Integer)opts.get("load_files")).intValue()!=0 )		iFlags |= 128;
			if ( ((Integer)opts.get("allow_empty")).intValue()!=0 )		iFlags |= 256;
			req.writeInt ( iFlags );
			writeNetUTF8 ( req, index );
			writeNetUTF8 ( req, words );

			/* send options */
			writeNetUTF8 ( req, (String) opts.get("before_match") );
			writeNetUTF8 ( req, (String) opts.get("after_match") );
			writeNetUTF8 ( req, (String) opts.get("chunk_separator") );
			req.writeInt ( ((Integer) opts.get("limit")).intValue() );
			req.writeInt ( ((Integer) opts.get("around")).intValue() );
			
			req.writeInt ( ((Integer) opts.get("limit_passages")).intValue() );
			req.writeInt ( ((Integer) opts.get("limit_words")).intValue() );
			req.writeInt ( ((Integer) opts.get("start_passage_id")).intValue() );
			writeNetUTF8 ( req, (String) opts.get("html_strip_mode") );

			/* send documents */
			req.writeInt ( docs.length );
			for ( int i=0; i<docs.length; i++ )
				writeNetUTF8 ( req, docs[i] );

			req.flush();

		} catch ( Exception e )
		{
			_error = "internal error: failed to build request: " + e;
			return null;
		}

		DataInputStream in = _DoRequest ( SEARCHD_COMMAND_EXCERPT, VER_COMMAND_EXCERPT, reqBuf );
		if ( in==null )
			return null;

		try
		{
			String[] res = new String [ docs.length ];
			for ( int i=0; i<docs.length; i++ )
				res[i] = readNetUTF8 ( in );
			return res;

		} catch ( Exception e )
		{
			_error = "incomplete reply";
			return null;
		}
	}



	/**
	 * Connect to searchd server and update given attributes on given documents in given indexes.
	 * Sample code that will set group_id=123 where id=1 and group_id=456 where id=3:
	 *
	 * <pre>
	 * String[] attrs = new String[1];
	 *
	 * attrs[0] = "group_id";
	 * long[][] values = new long[2][2];
	 *
	 * values[0] = new long[2]; values[0][0] = 1; values[0][1] = 123;
	 * values[1] = new long[2]; values[1][0] = 3; values[1][1] = 456;
	 *
	 * int res = cl.UpdateAttributes ( "test1", attrs, values );
	 * </pre>
	 *
	 * @param index		index name(s) to update; might be distributed
	 * @param attrs		array with the names of the attributes to update
	 * @param values	array of updates; each long[] entry must contains document ID
	 *					in the first element, and all new attribute values in the following ones
	 * @param ignorenonexistent	the flag whether to silently ignore non existent columns up update request
	 * @return			-1 on failure, amount of actually found and updated documents (might be 0) on success
	 *
	 * @throws			SphinxException on invalid parameters
	 */
	public int UpdateAttributes ( String index, String[] attrs, long[][] values, boolean ignorenonexistent ) throws SphinxException
	{
		/* check args */
		myAssert ( index!=null && index.length()>0, "no index name provided" );
		myAssert ( attrs!=null && attrs.length>0, "no attribute names provided" );
		myAssert ( values!=null && values.length>0, "no update entries provided" );
		for ( int i=0; i<values.length; i++ )
		{
			myAssert ( values[i]!=null, "update entry #" + i + " is null" );
			myAssert ( values[i].length==1+attrs.length, "update entry #" + i + " has wrong length" );
		}

		/* build and send request */
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();
		DataOutputStream req = new DataOutputStream ( reqBuf );
		try
		{
			writeNetUTF8 ( req, index );

			req.writeInt ( attrs.length );
			req.writeInt ( ignorenonexistent ? 1 : 0 );
			for ( int i=0; i<attrs.length; i++ )
			{
				writeNetUTF8 ( req, attrs[i] );
				req.writeInt ( 0 ); // not MVA attr
			}

			req.writeInt ( values.length );
			for ( int i=0; i<values.length; i++ )
			{
				req.writeLong ( values[i][0] ); /* send docid as 64bit value */
				for ( int j=1; j<values[i].length; j++ )
					req.writeInt ( (int)values[i][j] ); /* send values as 32bit values; FIXME! what happens when they are over 2^31? */
			}

			req.flush();

		} catch ( Exception e )
		{
			_error = "internal error: failed to build request: " + e;
			return -1;
		}

		/* get and parse response */
		DataInputStream in = _DoRequest ( SEARCHD_COMMAND_UPDATE, VER_COMMAND_UPDATE, reqBuf );
		if ( in==null )
			return -1;

		try
		{
			return in.readInt ();
		} catch ( Exception e )
		{
			_error = "incomplete reply";
			return -1;
		}
	}

	
	
	/**
	 * Connect to searchd server and update given MVA attributes on given document in given indexes.
	 * Sample code that will set group_id=(123, 456, 789) where id=10
	 *
	 * <pre>
	 * String[] attrs = new String[1];
	 *
	 * attrs[0] = "group_id";
	 * int[][] values = new int[1][3];
	 *
	 * values[0] = new int[3]; values[0][0] = 123; values[0][1] = 456; values[0][2] = 789
	 *
	 * int res = cl.UpdateAttributesMVA ( "test1", 10, attrs, values );
	 * </pre>
	 *
	 * @param index		index name(s) to update; might be distributed
	 * @param docid		id of document to update
	 * @param attrs		array with the names of the attributes to update
	 * @param values		array of updates; each int[] entry must contains all new attribute values
	 * @param ignorenonexistent	the flag whether to silently ignore non existent columns up update request
	 * @return			-1 on failure, amount of actually found and updated documents (might be 0) on success
	 *
	 * @throws			SphinxException on invalid parameters
	 */
	public int UpdateAttributesMVA ( String index, long docid, String[] attrs, int[][] values, boolean ignorenonexistent ) throws SphinxException
	{
		/* check args */
		myAssert ( index!=null && index.length()>0, "no index name provided" );
		myAssert ( docid>0, "invalid document id" );
		myAssert ( attrs!=null && attrs.length>0, "no attribute names provided" );
		myAssert ( values!=null && values.length>0, "no update entries provided" );
		myAssert ( values.length==attrs.length, "update entry has wrong length" );
		for ( int i=0; i<values.length; i++ )
		{
			myAssert ( values[i]!=null, "update entry #" + i + " is null" );
		}

		/* build and send request */
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();
		DataOutputStream req = new DataOutputStream ( reqBuf );
		try
		{
			writeNetUTF8 ( req, index );

			req.writeInt ( attrs.length );
			req.writeInt ( ignorenonexistent ? 1 : 0 );
			for ( int i=0; i<attrs.length; i++ )
			{
				writeNetUTF8 ( req, attrs[i] );
				req.writeInt ( 1 ); // MVA attr
			}

			req.writeInt ( 1 );
			req.writeLong ( docid ); /* send docid as 64bit value */
			
			for ( int i=0; i<values.length; i++ )
			{
				req.writeInt ( values[i].length ); /* send MVA's count */
				for ( int j=0; j<values[i].length; j++ ) /* send MVAs itself*/
					req.writeInt ( values[i][j] );
			}

			req.flush();

		} catch ( Exception e )
		{
			_error = "internal error: failed to build request: " + e;
			return -1;
		}

		/* get and parse response */
		DataInputStream in = _DoRequest ( SEARCHD_COMMAND_UPDATE, VER_COMMAND_UPDATE, reqBuf );
		if ( in==null )
			return -1;

		try
		{
			return in.readInt ();
		} catch ( Exception e )
		{
			_error = "incomplete reply";
			return -1;
		}
	}
	
	public int UpdateAttributes ( String index, String[] attrs, long[][] values ) throws SphinxException
	{
		return UpdateAttributes ( index, attrs, values, false );
	}

	public int UpdateAttributesMVA ( String index, long docid, String[] attrs, int[][] values ) throws SphinxException
	{
		return UpdateAttributesMVA ( index, docid, attrs, values, false );
	}

	/**
     * Connect to searchd server, and generate keyword list for a given query.
     * Returns null on failure, an array of Maps with misc per-keyword info on success.
     */
	public Map[] BuildKeywords ( String query, String index, boolean hits ) throws SphinxException
	{
		/* build request */
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();
		DataOutputStream req = new DataOutputStream ( reqBuf );
		try
		{
			writeNetUTF8 ( req, query );
			writeNetUTF8 ( req, index );
			req.writeInt ( hits ? 1 : 0 );

		} catch ( Exception e )
		{
			_error = "internal error: failed to build request: " + e;
			return null;
		}

		/* run request */
		DataInputStream in = _DoRequest ( SEARCHD_COMMAND_KEYWORDS, VER_COMMAND_KEYWORDS, reqBuf );
		if ( in==null )
			return null;

		/* parse reply */
		try
		{
			int iNumWords = in.readInt ();
			Map[] res = new Map[iNumWords];

			for ( int i=0; i<iNumWords; i++ )
			{
				res[i] = new LinkedHashMap ();
				res[i].put ( "tokenized", readNetUTF8 ( in ) );
				res[i].put ( "normalized", readNetUTF8 ( in ) );
				if ( hits )
				{
					res[i].put ( "docs", new Long ( readDword ( in ) ) );
					res[i].put ( "hits", new Long ( readDword ( in ) ) );
				}
			}
			return res;

		} catch ( Exception e )
		{
			_error = "incomplete reply";
			return null;
		}
	}



	/**
     * Force attribute flush, and block until it completes.
     * Returns current internal flush tag on success, -1 on failure.
     */
	public int FlushAttributes() throws SphinxException
	{
		/* build request */
		ByteArrayOutputStream reqBuf = new ByteArrayOutputStream();

		/* run request */
		DataInputStream in = _DoRequest ( SEARCHD_COMMAND_FLUSHATTRS, VER_COMMAND_FLUSHATTRS, reqBuf );
		if ( in==null )
			return -1;

		/* parse reply */
		try
		{
			int iFlushTag = in.readInt ();
			return iFlushTag;

		} catch ( Exception e )
		{
			_error = "incomplete reply";
			return -1;
		}
	}



	/** Escape the characters with special meaning in query syntax. */
	static public String EscapeString ( String s )
	{
		return s.replaceAll ( "([\\(\\)\\|\\-\\!\\@\\~\\\\\\\"\\&\\/\\^\\$\\=])", "\\\\$1" );
	}

	/** Open persistent connection to searchd. */
	public boolean Open()
	{
		if ( _socket!=null )
		{
			_error = "already connected";
			return false;
		}

		Socket sock = _Connect();
		if ( sock==null )
			return false;

		// command, command version = 0, body length = 4, body = 1
		try
		{
			DataOutputStream sOut = new DataOutputStream ( sock.getOutputStream() );
			sOut.writeShort ( SEARCHD_COMMAND_PERSIST );
			sOut.writeShort ( 0 );
			sOut.writeInt ( 4 );
			sOut.writeInt ( 1 );
		} catch ( IOException e )
		{
			_error = "network error: " + e;
			_connerror = true;
		}

		_socket = sock;
		return true;
	}

	/** Close existing persistent connection. */
	public boolean Close()
	{
		if ( _socket==null )
		{
			_error = "not connected";
			return false;
		}

		try
		{
			_socket.close();
		} catch ( IOException e )
		{}
		_socket = null;
		return true;
	}
}

/*
 * $Id: SphinxClient.java 3701 2013-02-20 18:10:18Z deogar $
 */
