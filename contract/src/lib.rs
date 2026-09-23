#![no_std]

use multiversx_sc::imports::*;

#[multiversx_sc::contract]
pub trait ServiceRegistry {
    #[init]
    fn init(&self) {}

    #[upgrade]
    fn upgrade(&self) {}

    /// Registra un evento de servicio.
    /// El caller (wallet del grupo) queda asociado automáticamente.
    #[endpoint(recordEvent)]
    fn record_event(
        &self,
        service_id: BigUint,
        company_id: BigUint,
        event_type: ManagedBuffer,
        timestamp: BigUint,
    ) {
        let caller = self.blockchain().get_caller();

        let event_str = ManagedBuffer::from(
            format!(
                "{}|{}|{}|{}",
                service_id.to_string(),
                company_id.to_string(),
                event_type.to_string(),
                timestamp.to_string()
            )
            .as_bytes()
        );

        let history = self.history(&caller);
        history.push(event_str);

        // Event log para exploradora
        sc::events::emit::<_, _, _, _, _>(
            &ManagedBuffer::from(b"ServiceEvent"),
            &caller,
            &service_id,
            &event_type,
            &timestamp,
        );
    }

    /// Devuelve la historia completa de una wallet (grupo).
    #[view(getHistory)]
    fn get_history(&self, caller: ManagedAddress) -> ManagedVec<ManagedBuffer> {
        self.history(&caller).as_vec()
    }

    /// Devuelve el número de eventos de una wallet.
    #[view(getCount)]
    fn get_count(&self, caller: ManagedAddress) -> BigUint {
        self.history(&caller).len()
    }

    #[storage_mapper("history")]
    fn history(&self, caller: &ManagedAddress) -> VectorMapper<ManagedBuffer>;
}