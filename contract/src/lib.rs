#![no_std]

use multiversx_sc::imports::*;

#[multiversx_sc::contract]
pub trait ServiceRegistry {
    #[init]
    fn init(&self) {}

    #[upgrade]
    fn upgrade(&self) {}

    /// Registra un evento de una solicitud concreta.
    /// Una empresa puede usar el mismo servicio varias veces: cada uso tiene su request_id.
    #[endpoint(recordEvent)]
    fn record_event(
        &self,
        request_id: BigUint,
        service_id: BigUint,
        company_id: BigUint,
        event_type: ManagedBuffer,
        timestamp: BigUint,
    ) {
        let caller = self.blockchain().get_caller();

        let event_str = sc_format!(
            "{}|{}|{}|{}|{}",
            request_id,
            service_id,
            company_id,
            event_type,
            timestamp
        );

        self.history(&caller).push(&event_str);

        self.service_event(&caller, &request_id, &service_id, &timestamp, &event_type);
    }

    /// Devuelve la historia completa de una wallet.
    #[view(getHistory)]
    fn get_history(&self, caller: ManagedAddress) -> ManagedVec<ManagedBuffer> {
        let mut history_vec = ManagedVec::new();
        for item in self.history(&caller).iter() {
            history_vec.push(item);
        }
        history_vec
    }

    /// Devuelve el número de eventos de una wallet.
    #[view(getCount)]
    fn get_count(&self, caller: ManagedAddress) -> usize {
        self.history(&caller).len()
    }

    #[event("ServiceEvent")]
    fn service_event(
        &self,
        #[indexed] caller: &ManagedAddress,
        #[indexed] request_id: &BigUint,
        #[indexed] service_id: &BigUint,
        #[indexed] timestamp: &BigUint,
        event_type: &ManagedBuffer,
    );

    #[storage_mapper("history")]
    fn history(&self, caller: &ManagedAddress) -> VecMapper<ManagedBuffer>;
}