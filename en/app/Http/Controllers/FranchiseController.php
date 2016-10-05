<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Auth;
use App\Custom\SSP;
use DB;
use App\Login;
use App\Franchise;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class FranchiseController extends Controller {

	//Call middleware to check for authentication
	public function __construct(){
		$this->middleware('auth');
	}

	/**
	 * Add a franchise.
	 *
	 * @param  Request object
	 * @return web-response
	 */
	public function addFranchise(Request $request){
		if( Auth::user()->login_type == 3 ){
			$validator = $this->validator($request->all(),false, true);

			if ($validator->fails()){
				$this->throwValidationException(
					$request, $validator
				);
			}
			
			$this->create($request->all(), true);

			return redirect()->back()
				   ->with('success','The franchise has been added.');
		}
		else{
			return redirect()->to('unauthorized');
		}
	}

	/**
	 * View manage franchise page.
	 *
	 * @param  None
	 * @return web-response
	 */
	public function manage_franchise(){
		
		if( Auth::user()->login_type <= 3)
			return view("project.manage.manage-franchise");
		else
			return view("errors.404");
	}

	/**
	 * Remove a Franchise.
	 *
	 * @param  Request object
	 * @return web-response
	 */
	public function deleteFranchise(Request $request){
		if( Auth::user()->login_type == 3 ){
			
			$del_ids = Input::get("del_ids");
			
			if( is_array($del_ids) ){
				foreach($del_ids as $id){
					$validator = $this->validator(['userid'=>$id], true,true);

					if ($validator->fails()){
						$this->throwValidationException(
							$request, $validator
						);
					}
				}
				$res = true;
				foreach($del_ids as $id){
					$user = Login::where( ["id"=>$id, "login_type"=>4] )->first();
					if( !is_null($user) && $res==true)
						$res = $user->forceDelete();
					else{
						return redirect()->back()->withErrors([
							'cant delete' => "Sorry!　There was an error processing your request.",
						]);
					}
				}
			}
			else{
				return redirect()->back()
						->withErrors([
							'userid' => 'Sorry!　There was an error processing your request.',
						]);
			}

			return redirect()->back()
					->with('success','The user has been deleted.');
		}
		else{
			return redirect()->to("unauthorized");
		}
	}

	

	/**
	 * Get a validator for an incoming registration/deletion request.
	 *
	 * @param  array  $data, $id for delete
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	private function validator(array $data, $id){
		

		if($id){
			$messages = array(
    			'userid.required'  => 'User ID is required.',
    			'userid.numeric'  => 'User ID must be numeric.'
			);
			return Validator::make($data, [
				'userid' => 'required|numeric',
			], $messages); 
		}
		else{
			$messages = array(
	    		'email.required' => 'Email address is required.',
	    		'email.email' => 'Please enter a valid email',
	    		'email.max' => 'Email must be smaller than 255 characters.',
	    		'name.required' => 'Name is required.',
	    		'name.max' => 'Name must be smaller than 255 characters.'
			);
			return Validator::make($data, [
				'name' => 'required|max:255',
				'email' => 'required|email|max:255|unique:users'
			], $messages);
		}
	}

	/**
	 * Create a new Franchise in the database.
	 *
	 * @param  array $data
	 * @return None
	 */
	private function create(array $data){
		
		DB::transaction(function($data) use ($data){
			$usernew = new Login;
			$usernew->login_type = 4;

			//TO-DO - create a arandom password and mail it to the user
			$usernew->password = bcrypt("spiderman");
			$usernew->save();

			$id_for_foreign = $usernew->id;
			$usernew->userid = $id_for_foreign;
			$usernew->save();

			$info_table = new Franchise;
			$info_table->id = $id_for_foreign;
			$info_table->name = $data["name"];
			$info_table->email = $data['email'];
			$info_table->save();
		});
	}

	/**
	 * Get franchise list from database.
	 *
	 * @param  None
	 * @return JSON
	 */
	public function viewFranchiseRecords(){
		if( Auth::user()->login_type <=3 ){
			$table = 'franchise';
			$primaryKey = 'id';

			$columns = array(
			    array( 'db' => '`l`.`userid`', 'dt' => 0, 'field' => 'userid' ),
			    array( 'db' => '`f`.`name`',  'dt' => 1, 'field' => 'name' ),
			    array( 'db' => '`f`.`email`',  'dt' => 2, 'field' => 'email' ),
			    array( 'db' => '`f`.`id`', 'dt' => 3, 'field' => 'id' )
			);

			$joinQuery = "FROM `{$table}` AS `f` JOIN `login` AS `l` ON (`l`.`id` = `f`.`id`)";
			
			$ssp_object = new SSP;
			return json_encode(
				$ssp_object->simple( $_GET, $table, $primaryKey, $columns, $joinQuery)
			);
		}
	}
}
