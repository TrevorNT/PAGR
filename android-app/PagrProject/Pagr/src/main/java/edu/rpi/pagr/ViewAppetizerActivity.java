package edu.rpi.pagr;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.actionbarsherlock.app.SherlockActivity;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.NameValuePair;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;

import java.util.ArrayList;

import edu.rpi.pagr.service.NotificationService;
import edu.rpi.pagr.utils.GatewayConnectionUtils;

public class ViewAppetizerActivity extends SherlockActivity {

    private AsyncTask<Void, Void, String> mCreateOrderTask;
    private Button button_oh_yeah;
    private String mUUID;
    private String mReservationID;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_appetizer);

        Intent intent = getIntent();
        mReservationID = (String) intent.getSerializableExtra("RESERVATION_ID");
        mUUID = (String) intent.getSerializableExtra("UUID");

        button_oh_yeah = (Button) findViewById(R.id.button_oh_yeah);
        button_oh_yeah.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mCreateOrderTask = new CreateOrderTask().execute();
            }
        });
    }

    private class CreateOrderTask extends AsyncTask<Void, Void, String> {

        @Override
        protected String doInBackground(Void... params) {
            try {
                if ( isCancelled() )
                    return null;

                // Send Values
                ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();
                nameValuePairs.add(new BasicNameValuePair("handset_id", mUUID ) ); // mUUID
                nameValuePairs.add(new BasicNameValuePair("reservation_id", mReservationID ) );

                HttpClient httpclient = new DefaultHttpClient();
                HttpPost httppost = new HttpPost(GatewayConnectionUtils.getApplicationBridgeBase()+GatewayConnectionUtils.getCreateOrder());

                httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

                HttpResponse response = httpclient.execute(httppost);

                StatusLine status = response.getStatusLine();

                if (status.getStatusCode() == HttpStatus.SC_OK) {
                    return new String(EntityUtils.toByteArray(response.getEntity()), "ISO-8859-1");
                }
            } catch (Exception ignored) {
            }

            return null;
        }

        @Override
        protected void onPostExecute(String result) {

            mCreateOrderTask = null;

            if (result != null) {
                try {
//                    int ID = Integer.parseInt( result );

                    Intent intent = new Intent(getBaseContext(), AppetizerFragmentActivity.class);
                    intent.putExtra("ORDER_ID", result);
                    intent.putExtra("RESERVATION_ID", mReservationID);
                    startActivity(intent);
                    finish();
                } catch (Exception ignored) {
                    Toast.makeText(getBaseContext(), result, Toast.LENGTH_SHORT).show();
                }
            }
        }
    }
}
