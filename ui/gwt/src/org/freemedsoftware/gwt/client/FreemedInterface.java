/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

package org.freemedsoftware.gwt.client;

import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.MainScreen;

import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.RootPanel;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class FreemedInterface implements EntryPoint {

	protected boolean active = false;

	protected LoginDialog loginDialog;

	protected MainScreen mainScreen;

	protected CurrentState state;

	/**
	 * This is the entry point method.
	 */
	public void onModuleLoad() {
		// Test to make sure we're logged in
		loginDialog = new LoginDialog();
		loginDialog.setFreemedInterface(this);
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			// Skip checking for logged in...
			loginDialog.center();
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = {};
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.public.Login.LoggedIn",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						if (200 == response.getStatusCode()) {
							Boolean r = (Boolean) JsonUtil.shoehornJson(
									JSONParser.parse(response.getText()),
									"Boolean");
							if (r != null) {
								if (r.booleanValue()) {
									// If logged in, continue
									resume();
								} else {
									// Force login loop
									loginDialog.center();
								}
							} else {
								loginDialog.center();
							}
						} else {
							Window.alert(response.toString());
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			LoginAsync service = null;
			try {
				service = (LoginAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Public.Login");

				service.LoggedIn(new AsyncCallback<Boolean>() {
					public void onSuccess(Boolean result) {
						if (result.booleanValue()) {
							// If logged in, continue
							resume();
						} else {
							// Force login loop
							loginDialog.center();
						}
					}

					public void onFailure(Throwable t) {
						Window.alert(t.getMessage());
						/*
						 * Window .alert("Unable to contact RPC service, try
						 * again later.");
						 */
					}
				});
			} catch (Exception e) {
				Window.alert("exception: " + e.getMessage());
			}
		}
	}

	public LoginDialog getLoginDialog() {
		return loginDialog;
	}

	public void resume() {
		if (!active) {
			mainScreen = new MainScreen();
			mainScreen.getCurrentState().assignLocale(
					loginDialog.getLanguageSelected());
			RootPanel.get("rootPanel").add(mainScreen);
			// mainScreen.setFreemedInterface(this);
			active = true;
		} else {
			RootPanel.setVisible(RootPanel.get("rootPanel").getElement(), true);
		}
	}

}
