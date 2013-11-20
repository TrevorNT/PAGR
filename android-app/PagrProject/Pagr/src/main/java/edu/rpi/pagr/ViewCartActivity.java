package edu.rpi.pagr;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.actionbarsherlock.app.SherlockFragmentActivity;

import edu.rpi.pagr.service.NotificationService;

public class ViewCartActivity extends SherlockFragmentActivity {

    private Button button_submit_order;
    private String mReservationID;
    private String mAppetizerID;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_cart);

        Intent intent = getIntent();
        mReservationID = (String) intent.getSerializableExtra("RESERVATION_ID");
        mAppetizerID = (String) intent.getSerializableExtra("APPETIZER_ID");

        button_submit_order = (Button) findViewById(R.id.button_submit_order);
        button_submit_order.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Toast.makeText(getBaseContext(), "We got your order!", Toast.LENGTH_SHORT).show();

                Intent serviceIntent = new Intent(getBaseContext(), NotificationService.class);
                startService(serviceIntent);
            }
        });
    }
    
}
