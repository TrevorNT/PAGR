package edu.rpi.pagr;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.FragmentTransaction;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.actionbarsherlock.app.SherlockFragmentActivity;

import edu.rpi.pagr.fragment.AppetizerDetailFragment;
import edu.rpi.pagr.fragment.AppetizerListFragment;
import edu.rpi.pagr.misc.Appetizer;
import edu.rpi.pagr.service.NotificationService;

/**
 * Created by Daniel Zhao on 11/6/13.
 */
public class AppetizerFragmentActivity extends SherlockFragmentActivity
        implements AppetizerListFragment.OnTitleSelectedListener {

    private String mReservationID;
    private Button button_proceed_to_checkout;
    private String mAppetizerID;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.list_appetizer_view);

        Intent intent = getIntent();
        mReservationID = (String) intent.getSerializableExtra("RESERVATION_ID");

        button_proceed_to_checkout = (Button) findViewById(R.id.button_proceed_to_checkout);
        button_proceed_to_checkout.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if ( mAppetizerID != null ) {
//                Toast.makeText(getBaseContext(), mAppetizerID, Toast.LENGTH_SHORT).show();

                    Intent intent = new Intent(getBaseContext(), ViewCartActivity.class);
                    intent.putExtra("ORDER_ITEM", mAppetizerID);
                    intent.putExtra("RESERVATION_ID", mReservationID);
                    startActivity(intent);
                }
            }
        });

        // Check whether the activity is using the layout version with
        // the fragment_container FrameLayout. If so, we must add the first fragment
        if (findViewById(R.id.fragment_container) != null) {

            // However, if we're being restored from a previous state,
            // then we don't need to do anything and should return or else
            // we could end up with overlapping fragments.
            if (savedInstanceState != null) {
                return;
            }

            // Create an instance of ExampleFragment
            AppetizerListFragment firstFragment = new AppetizerListFragment();

            // In case this activity was started with special instructions from an Intent,
            // pass the Intent's extras to the fragment as arguments
            firstFragment.setArguments(getIntent().getExtras());

            // Add the fragment to the 'fragment_container' FrameLayout
            getSupportFragmentManager().beginTransaction()
                    .add(R.id.fragment_container, firstFragment).commit();
        }
    }

    public void onAppetizerSelected(int position) {
        // The user selected the headline of an article from the HeadlinesFragment

        // Capture the article fragment from the activity layout
        AppetizerDetailFragment appetizerFrag = (AppetizerDetailFragment)
                getSupportFragmentManager().findFragmentById(R.id.appetizer_view);

        if (appetizerFrag != null) {
            // If article frag is available, we're in two-pane layout...

            // Call a method in the ArticleFragment to update its content
            appetizerFrag.updateAppetizerView(position);

        } else {
            // If the frag is not available, we're in the one-pane layout and must swap frags...

            // Create fragment and give it an argument for the selected article
            AppetizerDetailFragment newFragment = new AppetizerDetailFragment();
            Bundle args = new Bundle();
            args.putInt(AppetizerDetailFragment.ARG_POSITION, position);
            newFragment.setArguments(args);
            FragmentTransaction transaction = getSupportFragmentManager().beginTransaction();

            // Replace whatever is in the fragment_container view with this fragment,
            // and add the transaction to the back stack so the user can navigate back
            transaction.replace(R.id.fragment_container, newFragment);
            transaction.addToBackStack(null);

            // Commit the transaction
            transaction.commit();
        }
        mAppetizerID = Appetizer.AppetizerID[position];
    }
}