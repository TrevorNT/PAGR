package edu.rpi.pagr;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.app.Activity;
import android.os.Vibrator;
import android.view.Menu;

import edu.rpi.pagr.fragment.PagrDialogFragment;
import edu.rpi.pagr.R;

public class PagrDialogActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
//        setContentView(R.layout.v);

        PagrDialogFragment pagrDialogFragment = new PagrDialogFragment();
        pagrDialogFragment.show(getFragmentManager(), "PagrDialogFragment");
        Vibrator v = (Vibrator) getSystemService(Context.VIBRATOR_SERVICE);
        // Vibrate for 2000 milliseconds
        v.vibrate(1000);

        stopService(new Intent(this, NotificationService.class));
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.settings, menu);
        return true;
    }
    
}
